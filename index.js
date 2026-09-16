const { join } = require('path');

module.exports = {
  defaultValues: {
    namespace: 'ai-workshop',
    pluginURI: 'https://jinja.wordcamp.org/2026/speaker/joronimo-amanya/',
    version: '1.0.0',
    category: 'text',
    attributes: {
      prompt: { type: 'string', default: '' },
      content: { type: 'string', default: '' }
    }
  },
  templatesPath: join(__dirname, 'templates'),
};
