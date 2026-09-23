# create-wp-ai-block

[![npm version](https://img.shields.io/npm/v/create-wp-ai-block.svg)](https://www.npmjs.com/package/create-wp-ai-block)
[![License: GPL-2.0](https://img.shields.io/badge/License-GPL--2.0-blue.svg)](LICENSE)
[![Node Version](https://img.shields.io/badge/node-%3E%3D16.0.0-brightgreen.svg)](https://nodejs.org/)

> A zero-config CLI scaffolding tool for building production-ready, AI-powered Gutenberg block plugins for WordPress. Seamlessly integrates with Google Gemini (with automated multi-model failover) and OpenAI (GPT-4o-mini).

---

## 🚀 Quick Start (via NPX)

No installation required! Scaffold a new AI WordPress block anywhere using `npx`:

```bash
npx create-wp-ai-block my-ai-plugin
```

Or install globally:

```bash
npm install -g create-wp-ai-block
create-wp-ai-block my-ai-plugin
```

---

## ✨ What's New & Upgraded

### 1. 🛡️ Markdown-to-HTML Conversion (No Raw `**` or `##`)
- **Semantic Formatting**: AI responses are automatically transformed from raw Markdown into clean, semantic HTML elements (`<h2>`, `<h3>`, `<p>`, `<strong>`, `<em>`, `<ul>`, `<li>`).
- **Gutenberg RichText Native**: Eliminates unrendered markdown symbols (`**`, `##`, `*`) so content displays cleanly and can be styled, edited, and formatted directly inside Gutenberg's `<RichText>`.

### 2. 🏷️ Strict Name & Entity Preservation
- **Accurate Branding**: Integrated system instructions direct the AI model to faithfully retain all proper nouns, brand names, product names, titles, and custom entities from your prompt without swapping or altering them.
- **Smart Title Formatting**: The CLI intelligently preserves uppercase abbreviations (e.g., formatting `my-ai-block` into `My AI Block` instead of `My Ai Block`) and prompts for both slug and display title.

### 3. ⚡ Multi-Model Resilience & Auto-Failover (Google Gemini)
- **High Demand Protection**: When Google Gemini experiences temporary demand spikes (`HTTP 503: Spikes in demand`), the plugin automatically cascades through a resilient fallback pool:
  1. `gemini-3.6-flash` (Flagship fast modern model)
  2. `gemini-3.5-flash`
  3. `gemini-3.7-flash` (Advanced reasoning)
  4. `gemini-3.5-flash-lite` (Lightweight instant response)
  5. `gemini-flash-latest`
- **Zero Interruption**: Fallbacks execute in milliseconds on the server side without throwing 500 errors to the editor canvas.

### 4. 💻 Local Development Friendly (cURL / SSL Handshake Fix)
- **Zero-Friction Local Testing**: Integrated `'sslverify' => false` and 60-second timeouts for `wp_remote_post`. Eliminates the common `cURL error 60: SSL certificate problem` encountered on local environments such as **XAMPP**, **LocalWP**, **MAMP**, and **Docker**.

### 5. 🎨 Interactive In-Block Canvas & Inspector UI
- **Canvas-First Workflow**: Control your AI generation directly inside the block on the editor canvas—no need to constantly open and close the sidebar.
- **Quick Prompt Chips**: 1-click example chips (`Try an example:`) for testing and demonstration.
- **Live Status Badges**: Clear visual badges (`🤖 My AI Block`, `✓ Generated`, `Generating...`, `⚠ Error Notice`).
- **Full Inspector Controls**: Sidebar settings remain available for traditional WordPress workflows.

---

## 📁 Scaffolded Project Structure

```text
my-ai-plugin/
├── my-ai-plugin.php       # Main plugin file with REST API route (/my-ai-plugin/v1/generate)
├── package.json           # Scripts and @wordpress/scripts dependencies
├── src/
│   ├── block.json         # Block metadata, attributes, and styles declaration
│   ├── index.js           # Block registration entrypoint
│   ├── edit.js            # In-block interactive canvas & sidebar UI
│   ├── save.js            # Frontend markup renderer
│   ├── editor.scss        # Modern editor canvas styling
│   └── style.scss         # Frontend typography & container styling
└── build/                 # Compiled assets (generated via npm run build)
```

---

## 🛠️ Step-by-Step Usage

### 1. Scaffold the Plugin
Run the CLI tool and follow the interactive prompts:
```bash
npx create-wp-ai-block my-ai-plugin
```
Choose your preferred AI integration:
- **Google Gemini** (Recommended, multi-model failover)
- **OpenAI** (GPT-4o-mini)

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
Open your `wp-config.php` or the main plugin PHP file (`my-ai-plugin.php`) and define your API key:

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
4. Watch the clean, formatted HTML render directly into the block!

---

## 🔒 Security & Performance
- **Capability Checks**: The REST API generation endpoint requires `edit_posts` capabilities (`current_user_can('edit_posts')`), ensuring public visitors cannot trigger AI calls.
- **Sanitized Inputs**: All user prompts are sanitized via `sanitize_text_field()`.
- **Zero Leaked Keys**: API keys are defined server-side in PHP/`wp-config.php` and are never exposed in JavaScript or frontend bundles.

---

## 👤 Author

Developed with ❤️ by **Joronimo Amanya** ([@JORO-NIMO](https://github.com/JORO-NIMO)).

- **GitHub**: [https://github.com/JORO-NIMO/gutenberg-ai](https://github.com/JORO-NIMO/gutenberg-ai)
- **NPM Package**: [https://www.npmjs.com/package/create-wp-ai-block](https://www.npmjs.com/package/create-wp-ai-block)

---

## 📄 License
This project is licensed under the [GPL-2.0](LICENSE) license.
