[![Latest stable version]][packagist] [![Total downloads]][packagist] [![License]][packagist] [![GitHub forks]][fork] [![GitHub stars]][stargazers] [![GitHub watchers]][subscription]

# Carbon.IncludeAssetsCache Package for Neos CMS

Extend [Carbon.IncludeAssets] with an seperate cache entry for the included files.
If a file changes, the cache gets flushed. This is useful if you have got dynamic CSS or JavaScript files on your server.

## Installation

Most of the time, you have to make small adjustments to a package (e.g., the configuration in [`Settings.yaml`]). Because of that, it is important to add the corresponding package to the composer from your theme package. Mostly this is the site package located under `Packages/Sites/`. To install it correctly go to your theme package (e.g.`Packages/Sites/Foo.Bar`) and run following command:

```bash
composer require carbon/includeassetscache --no-update
```

The `--no-update` command prevent the automatic update of the dependencies. After the package was added to your theme `composer.json`, go back to the root of the Neos installation and run `composer update`. Et voilà! Your desired package is now installed correctly.

[packagist]: https://packagist.org/packages/carbon/includeassetscache
[latest stable version]: https://poser.pugx.org/carbon/includeassetscache/v/stable
[total downloads]: https://poser.pugx.org/carbon/includeassetscache/downloads
[license]: https://poser.pugx.org/carbon/includeassetscache/license
[github forks]: https://img.shields.io/github/forks/CarbonPackages/Carbon.IncludeAssetsCache.svg?style=social&label=Fork
[github stars]: https://img.shields.io/github/stars/CarbonPackages/Carbon.IncludeAssetsCache.svg?style=social&label=Stars
[github watchers]: https://img.shields.io/github/watchers/CarbonPackages/Carbon.IncludeAssetsCache.svg?style=social&label=Watch
[fork]: https://github.com/CarbonPackages/Carbon.IncludeAssetsCache/fork
[stargazers]: https://github.com/CarbonPackages/Carbon.IncludeAssetsCache/stargazers
[subscription]: https://github.com/CarbonPackages/Carbon.IncludeAssetsCache/subscription
[carbon.includeassets]: https://github.com/CarbonPackages/Carbon.IncludeAssets
