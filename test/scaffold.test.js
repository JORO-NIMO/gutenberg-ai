const test = require('node:test');
const assert = require('node:assert');
const path = require('node:path');
const fs = require('fs-extra');
const { execSync } = require('node:child_process');

const CLI_PATH = path.resolve(__dirname, '../bin/index.js');
const TMP_DIR = path.resolve(__dirname, '../.tmp_test_output');

test.beforeEach(async () => {
  await fs.ensureDir(TMP_DIR);
});

test.afterEach(async () => {
  await fs.remove(TMP_DIR);
});

test('CLI --help displays options including --title, --provider, and --skip-install', () => {
  const output = execSync(`node "${CLI_PATH}" --help`).toString();
  assert.ok(output.includes('--title'));
  assert.ok(output.includes('--provider'));
  assert.ok(output.includes('--skip-install'));
});

test('CLI rejects invalid project slug with uppercase or special characters', () => {
  let failed = false;
  try {
    execSync(`node "${CLI_PATH}" "Invalid_Slug!" --skip-install`, { cwd: TMP_DIR, stdio: 'pipe' });
  } catch (err) {
    failed = true;
    assert.strictEqual(err.status, 1);
  }
  assert.ok(failed, 'Should have exited with status 1 for invalid slug');
});

test('CLI scaffolds a mock block plugin cleanly without remaining placeholders', async () => {
  const slug = 'test-demo-block';
  const title = 'Test Demo Block';
  execSync(`node "${CLI_PATH}" ${slug} --provider mock --title "${title}" --skip-install`, {
    cwd: TMP_DIR,
    stdio: 'pipe'
  });

  const projectDir = path.join(TMP_DIR, slug);
  assert.ok(fs.existsSync(projectDir), 'Project directory should exist');

  const mainPhpFile = path.join(projectDir, `${slug}.php`);
  assert.ok(fs.existsSync(mainPhpFile), 'Main PHP file should exist');

  const phpContent = await fs.readFile(mainPhpFile, 'utf8');
  assert.ok(phpContent.includes(`Plugin Name:       ${title}`));
  assert.ok(phpContent.includes('function test_demo_block_block_init()'));
  assert.ok(phpContent.includes("register_rest_route( 'test-demo-block/v1', '/generate'"));
  assert.ok(phpContent.includes("$provider    = 'mock';"));
  assert.ok(phpContent.includes('test_demo_block_settings_group'));
  assert.ok(!phpContent.includes('{{'), 'No unreplaced template placeholders should remain in PHP');

  const blockJsonPath = path.join(projectDir, 'src/block.json');
  assert.ok(fs.existsSync(blockJsonPath), 'block.json should exist');
  const blockJson = await fs.readJson(blockJsonPath);
  assert.strictEqual(blockJson.name, `create-wp-ai-block/${slug}`);
  assert.strictEqual(blockJson.title, title);
  assert.ok(blockJson.attributes.tone);
  assert.ok(blockJson.attributes.temperature);

  const editJs = await fs.readFile(path.join(projectDir, 'src/edit.js'), 'utf8');
  assert.ok(editJs.includes("path: '/test-demo-block/v1/generate'"));
  assert.ok(!editJs.includes('{{'), 'No unreplaced template placeholders should remain in edit.js');

  const packageJson = await fs.readJson(path.join(projectDir, 'package.json'));
  assert.strictEqual(packageJson.name, slug);
  assert.strictEqual(packageJson.description, `${title} WordPress Block`);
});

test('CLI scaffolds a Google Gemini block with correct failover models and settings page', async () => {
  const slug = 'gemini-writer-ai';
  execSync(`node "${CLI_PATH}" ${slug} -p gemini -s`, {
    cwd: TMP_DIR,
    stdio: 'pipe'
  });

  const projectDir = path.join(TMP_DIR, slug);
  const mainPhpFile = path.join(projectDir, `${slug}.php`);
  const phpContent = await fs.readFile(mainPhpFile, 'utf8');

  assert.ok(phpContent.includes("'gemini-2.0-flash'"));
  assert.ok(phpContent.includes("'gemini-1.5-flash'"));
  assert.ok(phpContent.includes("GEMINI_API_KEY"));
  assert.ok(phpContent.includes("gemini_writer_ai_render_settings_page"));
  assert.ok(!phpContent.includes('{{'), 'No unreplaced template placeholders should remain');
});
