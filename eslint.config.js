import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default [
  {
    ignores: [
      'bootstrap/cache/**',
      'node_modules/**',
      'public/build/**',
      'storage/**',
      'vendor/**',
    ],
  },
  js.configs.recommended,
  ...tseslint.configs.recommended,
  ...pluginVue.configs['flat/essential'],
  {
    files: ['resources/js/**/*.{js,ts,vue}'],
    languageOptions: {
      ecmaVersion: 'latest',
      globals: {
        ...globals.browser,
        ...globals.node,
      },
      parserOptions: {
        parser: tseslint.parser,
      },
      sourceType: 'module',
    },
    rules: {
      'vue/first-attribute-linebreak': ['error', {
        multiline: 'below',
        singleline: 'ignore',
      }],
      'vue/html-indent': ['error', 2, {
        attribute: 1,
        baseIndent: 1,
        closeBracket: 0,
      }],
      'vue/multiline-html-element-content-newline': 'error',
      'vue/max-attributes-per-line': ['error', {
        multiline: {
          max: 1,
        },
        singleline: {
          max: 1,
        },
      }],
      'vue/multi-word-component-names': 'off',
      'vue/no-v-html': 'off',
      'vue/singleline-html-element-content-newline': ['error', {
        ignoreWhenNoAttributes: false,
      }],
    },
  },
];
