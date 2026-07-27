# CONTRIBUTING

Contributions are welcome, and are accepted via pull requests. Please review these guidelines before submitting any pull requests.

## Guidelines

  * Please follow the [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) Coding Standard, PHP-FIG Naming Conventions and the [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) autoloading standard
  * Ensure that the current tests pass, and if you've added something new, add the tests where relevant.
  * Remember that we follow SemVer. If you are changing the behaviour, or the public api, you may need to update the docs.
  * Send a coherent commit history, making sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.
  * You may also need to rebase to avoid merge conflicts.
  * **Translations :**
    * After you made your translations, ensure that you added your locale key in alphabetical order in the [`tests/TestCase.php`](https://github.com/iYogesharma/logviewer-laravel/blob/master/tests/TestCase.php) file for the tests.
    * The locale key must be a [ISO 639-1 code](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes), check also [caouecs/Laravel-lang package](https://github.com/caouecs/Laravel-lang/).

## Running Tests

You will need an install of [Composer](https://getcomposer.org) before continuing.

First, install the dependencies:

```bash
$ composer install
```

Then run phpunit:

```bash
$ vendor/bin/phpunit
```

If the test suite passes on your local machine you should be good to go.

When you open a pull request, the tests are run again by [GitHub Actions](https://github.com/iYogesharma/logviewer-laravel/actions).
