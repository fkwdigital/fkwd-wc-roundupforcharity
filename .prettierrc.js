const wordPressConfig = require('@wordpress/prettier-config');

module.exports = {
    ...wordPressConfig,
    useTabs: false,
    tabWidth: 4,
    singleQuote: true,
    plugins: [...(wordPressConfig.plugins || []), '@prettier/plugin-php'],
    phpVersion: '8.3',
    overrides: [
        {
            files: ['*.scss', '*.sass'],
            options: {
                parser: 'scss',
                printWidth: 9999,
                proseWrap: 'preserve',
            },
        },
        ...(wordPressConfig.overrides || []),
    ],
};
