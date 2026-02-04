# Parish Distribution - Jetpack Social

A destination plugin for [Parish Distribution](https://github.com/nonatech-uk/wordpress-parish-distribution) that integrates Jetpack Social (Publicize) for controlled Facebook sharing.

## How it works

When Jetpack Social is about to share a published post, this plugin intercepts the decision via the `publicize_should_publicize_published_post` filter. Sharing only proceeds if the "Jetpack Social" checkbox in the Distribution sidebar panel was checked.

This gives editors explicit per-post control over social sharing, rather than Jetpack's default behaviour of sharing every new post automatically.

## Requirements

- WordPress 5.0+
- PHP 7.4+
- [Parish Distribution](https://github.com/nonatech-uk/wordpress-parish-distribution) plugin
- Jetpack with Publicize module enabled, or Jetpack Social standalone plugin

## Post meta

| Key | Type | Description |
|-----|------|-------------|
| `_parish_dist_jetpack_social` | boolean | Whether sharing is enabled for the post |
| `_parish_dist_jetpack_social_at` | string | Timestamp when sharing was allowed |
