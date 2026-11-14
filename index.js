const { join } = require('path');

module.exports = {
  defaultValues: {
    namespace: 'ai-block',
    version: '1.0.0',
    category: 'widgets',
    attributes: {
      prompt: { type: 'string', default: '' },
      response: { type: 'string', default: '' },
      status: { type: 'string', default: 'idle' },
      tone: { type: 'string', default: 'professional' },
      temperature: { type: 'number', default: 0.7 }
    }
  },
  templatesPath: join(__dirname, 'templates'),
};
