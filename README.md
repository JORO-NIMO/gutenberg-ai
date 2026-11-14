# create-wp-ai-block

[![npm version](https://img.shields.io/npm/v/create-wp-ai-block.svg)](https://www.npmjs.com/package/create-wp-ai-block)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-blue.svg)](LICENSE)
[![Node Version](https://img.shields.io/badge/node-%3E%3D16.0.0-brightgreen.svg)](https://nodejs.org/)

> A zero-config CLI scaffolding tool for building production-ready, AI-powered Gutenberg block plugins for WordPress. Seamlessly integrates with Google Gemini (with automated multi-model failover), OpenAI (GPT-4o-mini), and a zero-key Mock mode for instant testing and workshops.

---

## 🚀 Quick Start (via NPX)

No installation required! Scaffold a new AI WordPress block anywhere using `npx`:

```bash
npx create-wp-ai-block my-ai-plugin
```

Or pass flags directly:

```bash
# Using Google Gemini with custom title
npx create-wp-ai-block my-ai-plugin --title "My AI Plugin" --provider gemini

# Instant demo mode without API keys
npx create-wp-ai-block my-ai-plugin --provider mock --skip-install
```

### CLI Flags & Options

| Option | Shorthand | Description | Default |
|---|---|---|---|
| `[project-name]` | — | Slug / directory name of the block plugin | Interactive prompt |
| `--title <name>` | `-t` | Display title of the block plugin | Derived from slug |
| `--provider <type>` | `-p` | AI provider (`gemini`, `openai`, `mock`) | Interactive prompt |
| `--skip-install` | `-s` | Skip automatic `npm install` | `false` |

---

## ✨ Features & Architecture

### 1. 🛡️ Extended Markdown-to-HTML Conversion
- **Semantic Formatting**: AI responses are automatically transformed from raw Markdown into clean, semantic HTML elements (`<h2>`, `<h3>`, `<h4>`, `<h5>`, `<h6>`, `<p>`, `<strong>`, `<em>`, `<ul>`, `<ol>`, `<li>`, `<blockquote>`, `<a>`).
- **Gutenberg RichText Native**: Eliminates unrendered markdown symbols (`**`, `##`, `*`, `[text](url)`) so content displays cleanly and can be styled, edited, and formatted directly inside Gutenberg's `<RichText>`.

### 2. 🎛️ Configurable Tone & Creativity (Temperature)
- **Content Tones**: Select between *Professional*, *Casual & Friendly*, *Inspiring & Engaging*, *Technical & Precise*, or *Concise Summary* directly in the block Inspector.
- **Creativity Control**: Adjust temperature directly with a slider (0.1 to 1.5) to fine-tune response focus.

### 3. ⚡ Multi-Model Resilience & Auto-Failover (Google Gemini)
- **High Demand Protection**: When Google Gemini experiences temporary demand spikes or rate limits, the plugin cascades through an automated fallback pool:
  1. `gemini-2.0-flash` (Flagship fast modern model)
  2. `gemini-1.5-flash` (High performance)
  3. `gemini-1.5-flash-8b` (Ultra-low latency)
  4. `gemini-2.5-flash`
- **Zero Interruption**: Fallbacks execute in milliseconds on the server side without throwing 500 errors to the editor canvas.

### 4. 🧪 Zero-Key Mock / Demo Mode
- Scaffold and test fully functional Gutenberg AI blocks without configuring external API keys. Ideal for client demos, workshops, and offline local development.

### 5. ⚙️ WordPress Admin Settings Page
- Configure your OpenAI or Google Gemini API key via the standard WordPress admin panel under **Settings → {Plugin Name}**, or continue using `wp-config.php` constants (`OPENAI_API_KEY` / `GEMINI_API_KEY`).

### 6. 🔒 Environment-Aware SSL Verification
- Automatically adapts SSL verification: skips strict SSL checks when developing locally (e.g., LocalWP, XAMPP, Docker) to avoid cURL 60 errors, while enforcing full SSL verification in production environments.

### 7. 🎨 Interactive In-Block Canvas & Inspector UI
- **Canvas-First Workflow**: Control your AI generation directly inside the block on the editor canvas—no need to constantly open and close the sidebar.
- **Quick Prompt Chips**: 1-click example chips (`Try an example:`) for testing and demonstration.
- **Live Status Badges**: Clear visual badges (`🤖 My AI Block`, `✓ Generated`, `Generating...`, `⚠ Error Notice`).

---

## 📁 Scaffolded Project Structure

```text
my-ai-plugin/
├── my-ai-plugin.php       # Main plugin file with REST API route (/my-ai-plugin/v1/generate) and Settings page
├── package.json           # Scripts and @wordpress/scripts dependencies
├── src/
│   ├── block.json         # Block metadata, attributes (prompt, response, tone, temperature), and styles
│   ├── index.js           # Block registration entrypoint
│   ├── edit.js            # In-block interactive canvas & sidebar controls
│   ├── save.js            # Frontend markup renderer
│   ├── editor.scss        # Modern editor canvas styling
│   └── style.scss         # Frontend typography & container styling
└── build/                 # Compiled assets (generated via npm run build)
```

---

## 🛠️ Step-by-Step Usage

### 1. Scaffold the Plugin
```bash
npx create-wp-ai-block my-ai-plugin
```
Choose your preferred AI integration:
- **Google Gemini** (Recommended, multi-model failover)
- **OpenAI** (GPT-4o-mini)
- **Mock / Demo Mode** (Instant testing without API keys)

### 2. Build Block Assets
Navigate into your newly scaffolded plugin directory:
```bash
cd my-ai-plugin
npm run build
```
> **Tip**: Run `npm start` while developing to automatically watch and compile changes in real time.

### 3. Add to WordPress
Copy or symlink your plugin folder into your WordPress plugins directory:
```text
wp-content/plugins/my-ai-plugin/
```

### 4. Activate in Dashboard
1. Open your WordPress admin dashboard (`wp-admin`).
2. Go to **Plugins** → **Installed Plugins**.
3. Locate **My AI Block** and click **Activate**.

### 5. Configure Your API Key
You can configure your key in either of two ways:
1. **Admin Dashboard**: Go to **Settings** → **My AI Block** and paste your API key.
2. **wp-config.php**: Define the key constant:
   ```php
   // If you selected Google Gemini:
   define( 'GEMINI_API_KEY', 'your-gemini-api-key-here' );

   // If you selected OpenAI:
   define( 'OPENAI_API_KEY', 'your-openai-api-key-here' );
   ```

### 6. Test Your Block
1. Create or edit any WordPress post or page in the Gutenberg editor.
2. Click the **+** (Block Inserter) and search for your block name (e.g., **My AI Block**).
3. Insert the block, choose a quick suggestion chip or type your prompt, and hit **✨ Generate AI Content**.
4. Edit and refine the formatted HTML directly in the block!

---

## 🔒 Security & Performance
- **Capability Checks**: The REST API generation endpoint requires `edit_posts` capabilities (`current_user_can('edit_posts')`), ensuring public visitors cannot trigger AI calls.
- **Sanitized Inputs**: All user prompts are sanitized via `sanitize_text_field()`.
- **Zero Leaked Keys**: API keys are stored server-side in PHP/database options and are never exposed in JavaScript or frontend bundles.

---

## 👤 Author

Developed with ❤️ by **Joronimo Amanya** ([@JORO-NIMO](https://github.com/JORO-NIMO)).

- **GitHub**: [https://github.com/JORO-NIMO/gutenberg-ai](https://github.com/JORO-NIMO/gutenberg-ai)
- **NPM Package**: [https://www.npmjs.com/package/create-wp-ai-block](https://www.npmjs.com/package/create-wp-ai-block)

---

## 📄 License
This project is licensed under the [GPL-2.0-or-later](LICENSE) license.
