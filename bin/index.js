#!/usr/bin/env node

const { program } = require('commander');
const prompts = require('prompts');
const chalk = require('chalk');
const fs = require('fs-extra');
const path = require('path');
const { execSync } = require('child_process');

program
  .name('create-wp-ai-block')
  .description('Scaffold a zero-auth WordPress AI block plugin')
  .argument('[project-name]', 'The name of the block plugin directory')
  .option('-p, --provider <type>', 'The AI provider to use (openai, gemini, mock)')
  .parse(process.argv);

const options = program.opts();
let projectName = program.args[0];
let projectTitle = '';

async function run() {
  if (!projectName) {
    const nameResponse = await prompts([
      {
        type: 'text',
        name: 'slug',
        message: 'What is the slug of your WP AI Block project (folder name)?',
        initial: 'my-ai-block',
        validate: (value) => (value && value.match(/^[a-z0-9\-]+$/) ? true : 'Project slug may only contain lowercase letters, numbers, and dashes.')
      },
      {
        type: 'text',
        name: 'title',
        message: 'What is the display title of your block plugin?',
        initial: (prev) => prev ? prev.split('-').map(word => word.toLowerCase() === 'ai' ? 'AI' : word.charAt(0).toUpperCase() + word.slice(1)).join(' ') : 'My AI Block'
      }
    ]);
    projectName = nameResponse.slug;
    projectTitle = nameResponse.title;
  }

  if (!projectName) {
    console.log(chalk.red('Project name is required. Exiting.'));
    process.exit(1);
  }

  let provider = options.provider;
  if (!provider) {
    const aiChoice = await prompts({
      type: 'select',
      name: 'provider',
      message: 'Which AI integration would you like to use?',
      choices: [
        { title: 'OpenAI (GPT-4o-mini)', value: 'openai' },
        { title: 'Google Gemini (Flash models with auto-failover)', value: 'gemini' }
      ]
    });
    provider = aiChoice.provider;
  }
  if (!provider) {
    console.log(chalk.red('AI provider selection is required. Exiting.'));
    process.exit(1);
  }

  const targetDir = path.resolve(process.cwd(), projectName);
  const templateDir = path.resolve(__dirname, '../templates');

  if (fs.existsSync(targetDir)) {
    console.log(chalk.red(`Directory ${projectName} already exists. Please choose a different name.`));
    process.exit(1);
  }

  console.log(chalk.cyan(`\nScaffolding block plugin in ${targetDir}...`));

  try {
    // Copy template
    await fs.copy(templateDir, targetDir);

    // Dynamic String Replacements
    const blockSlug = projectName.toLowerCase().replace(/[^a-z0-9\-]/g, '-');
    const pluginName = projectTitle || projectName
      .split(/[-_ ]+/)
      .map(word => word.toLowerCase() === 'ai' ? 'AI' : word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
    
    const functionPrefix = blockSlug.replace(/-/g, '_');

    const replaceInFile = async (filePath, replacements) => {
      let content = await fs.readFile(filePath, 'utf8');
      for (const [search, replace] of Object.entries(replacements)) {
        content = content.replace(new RegExp(search, 'g'), replace);
      }
      await fs.writeFile(filePath, content, 'utf8');
    };

    // Rename plugin.php to {{BLOCK_SLUG}}.php
    const phpFilePath = path.join(targetDir, 'plugin.php');
    const newPhpFilePath = path.join(targetDir, `${blockSlug}.php`);
    await fs.rename(phpFilePath, newPhpFilePath);

    const filesToProcess = [
      newPhpFilePath,
      path.join(targetDir, 'package.json'),
      path.join(targetDir, 'src/block.json'),
      path.join(targetDir, 'src/edit.js'),
      path.join(targetDir, 'src/index.js'),
      path.join(targetDir, 'src/save.js'),
      path.join(targetDir, 'src/editor.scss'),
      path.join(targetDir, 'src/style.scss')
    ];

    const replacements = {
      '{{PLUGIN_NAME}}': pluginName,
      '{{BLOCK_SLUG}}': blockSlug,
      '{{FUNCTION_PREFIX}}': functionPrefix,
      '{{AI_PROVIDER}}': provider
    };

    for (const file of filesToProcess) {
      if (fs.existsSync(file)) {
        await replaceInFile(file, replacements);
      }
    }

    console.log(chalk.green('\nTemplates copied and configured.'));

    // Install dependencies
    console.log(chalk.cyan('\nInstalling dependencies (npm install)...'));
    console.log(chalk.gray('(This usually takes 1-3 minutes because it downloads WordPress Webpack & React build tools)'));
    execSync('npm install --no-audit --no-fund', { cwd: targetDir, stdio: 'inherit' });

    console.log(chalk.green(`\nSuccess! Created ${pluginName} at ${targetDir}`));
    console.log(chalk.cyan('\nInside that directory, you can run several commands:'));
    console.log(chalk.white('  npm start'));
    console.log(chalk.gray('    Starts the build for development.'));
    console.log(chalk.white('  npm run build'));
    console.log(chalk.gray('    Builds the code for production.'));
    
    console.log(chalk.cyan('\nGet started by typing:'));
    console.log(chalk.white(`  cd ${projectName}`));
    console.log(chalk.white('  npm start\n'));

    console.log(chalk.yellow(`Don't forget to activate your plugin in WordPress and add your API keys to the ${blockSlug}.php file if you chose a real AI provider!`));

  } catch (error) {
    console.error(chalk.red('\nAn error occurred during scaffolding:'), error);
    process.exit(1);
  }
}

run();
