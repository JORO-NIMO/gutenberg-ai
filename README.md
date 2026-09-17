# create-wp-ai-block

A zero-config CLI tool for scaffolding AI-powered WordPress blocks. It sets up a fully functional Gutenberg block plugin that connects directly to AI APIs like OpenAI or Google Gemini out of the box.

## Requirements

- Node.js
- npm
- WordPress environment (local or live)

## Installation & Usage

You don't need to install this globally. You can use `npx` to run it directly:

```bash
npx create-wp-ai-block my-ai-plugin
```

If you prefer to install it globally:

```bash
npm install -g create-wp-ai-block
create-wp-ai-block my-ai-plugin
```

### Setup Process

1. When you run the command, it will ask you for a project name (if you didn't provide one).
2. It will prompt you to select an AI provider:
   - **OpenAI (GPT-4o-mini)**
   - **Google Gemini (gemini-1.5-flash)**
3. The CLI will generate a complete WordPress block plugin inside a new folder matching your project name, replace all placeholders, and install the necessary dependencies via `npm install`.

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
   Open the main plugin PHP file (e.g., `my-ai-plugin.php`) and add your actual API key for the provider you chose. For example:
   ```php
   define( 'OPENAI_API_KEY', 'your-actual-api-key-here' );
   ```

5. **Test it out**:
   Open any post or page, add your new block, and look at the right-hand Inspector Sidebar. Type a prompt, hit generate, and watch the AI content render in your block!

## License
GPL-2.0
