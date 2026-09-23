# create-wp-ai-block

A zero-config CLI tool for scaffolding AI-powered WordPress blocks. It sets up a fully functional Gutenberg block plugin that connects directly to AI APIs like OpenAI or Google Gemini out of the box.

## Requirements

- Node.js
- npm
- WordPress environment (local or live)

## 🎉 Now Available on NPM!

`create-wp-ai-block` is officially published to the NPM registry! You don't need to clone this repository or install anything globally. 

Anyone can scaffold a new AI block anywhere on their computer instantly using `npx`:

```bash
npx create-wp-ai-block my-ai-plugin
```

If you prefer to install it globally:

```bash
npm install -g create-wp-ai-block
create-wp-ai-block my-ai-plugin
```

### Setup Process

1. When you run the command, it will ask you for a project slug and display title (if you didn't provide one).
2. It will prompt you to select an AI provider:
   - **OpenAI (GPT-4o-mini)**
   - **Google Gemini (Flash models with multi-model auto-failover)**
3. The CLI will generate a complete WordPress block plugin inside a new folder matching your project name, replace all placeholders, and install the necessary dependencies via `npm install`.

## ✨ Features

- **In-Block Canvas & Sidebar Controls**: Interactive UI directly inside the editor canvas with quick prompt suggestion chips, progress indicators, clear button, and inspector sidebar controls.
- **Clean HTML Formatting (No Raw Markdown)**: AI output is automatically converted to semantic HTML (`<p>`, `<h3>`, `<strong>`, `<ul>`, `<li>`), ensuring no raw `**` or `##` markdown characters appear in Gutenberg.
- **Name & Entity Preservation**: Strict system instructions ensure all specific names, brands, titles, and entities in the prompt are preserved without alteration.
- **Multi-Model Auto-Failover (Gemini)**: Cascades through `gemini-3.6-flash`, `gemini-3.5-flash`, `gemini-3.7-flash`, and `gemini-flash-latest` so your block never fails during temporary provider demand spikes.
- **Local Dev Friendly**: Built-in SSL verification bypass for local environments (XAMPP, LocalWP, Docker, Windows, macOS, Linux).

## Using the Scaffolded Plugin

After the scaffolding finishes, navigate into your new project folder:

```bash
cd my-ai-plugin
```

1. **Build the block assets**:
   ```bash
   npm run build
   ```
   *Note: Use `npm start` while developing to automatically recompile your code when you save.*

2. **Add to WordPress**:
   Copy the `my-ai-plugin` folder into your WordPress `wp-content/plugins/` directory.

3. **Activate the Plugin**:
   Log into your WordPress admin dashboard, go to Plugins, and activate it.

4. **Add your API Key**:
   Open the main plugin PHP file (e.g., `my-ai-plugin.php`) or your `wp-config.php` and add your API key for the provider you chose. For example:
   ```php
   // If using OpenAI:
   define( 'OPENAI_API_KEY', 'your-actual-api-key-here' );

   // If using Google Gemini:
   define( 'GEMINI_API_KEY', 'your-actual-api-key-here' );
   ```

5. **Test it out**:
   Open any post or page, insert your new block from the block inserter, enter a prompt (or click one of the quick suggestions), hit **Generate AI Content**, and see the formatted content appear right in the block!

## License
GPL-2.0
