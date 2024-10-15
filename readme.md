This contains the source files for the "*Omnipedia - Pipeline*" Drupal module,
which provides [Pipeline](https://pyrofoux.itch.io/neurocracy2049) integration
and functionality for [Omnipedia](https://omnipedia.app/).

⚠️ ***[Why open source? / Spoiler warning](https://omnipedia.app/open-source)***

----

# Requirements

* [Drupal 10.3 or 11](https://www.drupal.org/download)

* PHP 8.1

* [Composer](https://getcomposer.org/)

----

# Installation

## Composer

### Set up

Ensure that you have your Drupal installation set up with the correct Composer
installer types such as those provided by [the `drupal/recommended-project`
template](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates#s-drupalrecommended-project).
If you're starting from scratch, simply requiring that template and following
[the Drupal.org Composer
documentation](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates)
should get you up and running.

### Repository

In your root `composer.json`, add the following to the `"repositories"` section:

```json
"drupal/omnipedia_pipeline": {
  "type": "vcs",
  "url": "https://github.com/neurocracy/drupal-omnipedia-pipeline.git"
}
```

### Installing

Once you've completed all of the above, run `composer require
"drupal/omnipedia_pipeline:^1.0@dev"` in the root of your project to have
Composer install this and its required dependencies for you.
