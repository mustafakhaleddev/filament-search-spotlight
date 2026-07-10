# Filament Search Spotlight

A full-screen Spotlight / command-palette search overlay for Filament
panels. Opens on ⌘K (configurable), aggregates results from multiple categories
(records, resources, pages, actions, plus recent/pinned from localStorage), and
composes Filament's built-in `GlobalSearchProvider` — every resource that
already implements `getGloballySearchableAttributes()` shows up automatically.

## Features

- ⌘K / Ctrl+K overlay, single-column centered layout
- Categories out of the box:
  - **Records** — delegates to the panel's `GlobalSearchProvider`, attaches the
    resource's navigation icon to each result
  - **Resources** — jump to any resource's index page by its plural label
  - **Pages** — fuzzy-matches standalone panel pages by navigation label
  - **Actions** — fluent `SpotlightAction` registry + auto-generated
    "Create {Resource}" entries for resources with a `create` page
- Recent & pinned items persisted in browser `localStorage` (no DB, no
  migrations)
- Keyboard navigation: arrow keys wrap, enter activates, escape closes
- Single-click (or enter) to activate; hover syncs the highlighted row
- Fully configurable per panel and globally via config file

## Installation

```bash
composer require wezlo/filament-search-spotlight
```

Register the plugin on your panel:

```php
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;

->plugins([
    FilamentSearchSpotlightPlugin::make(),
])
```

That's it — press ⌘K inside any panel page.

If your panel uses a compiled Tailwind theme (e.g.
`resources/css/filament/admin/theme.css`), make sure it scans the package's
views so utility classes are generated:

```css
@source '../../../../vendor/wezlo/filament-search-spotlight/resources/views/**/*';
```

Then rebuild with `npm run build` / `npm run dev`.

## Configuration

Publish the config file (optional):

```bash
php artisan vendor:publish --tag=filament-search-spotlight-config
```

Every option is settable fluently on the plugin or in
`config/filament-search-spotlight.php`. Fluent calls win over config values.

### Fluent API

```php
FilamentSearchSpotlightPlugin::make()
    // Keyboard binding (Mousetrap syntax). Accepts a string or an array.
    ->keyBinding('mod+k')

    // Placeholder text for the search input.
    ->placeholder('Jump to…')

    // Any valid CSS width (rem, px, vw, %, …). Applied as an inline style so
    // it is not subject to Tailwind purging.
    ->maxWidth('36rem')

    // Max results per category.
    ->resultLimitPerCategory(8)

    // Toggle built-in categories (all default on).
    ->records()           // records(false) to hide
    ->resources()
    ->pages()
    ->actionsEnabled()

    // Or completely override the category list with your own.
    ->categories([MyCustomCategory::class])

    // Exclude resources from both the Records and Resources categories
    // (also prevents their auto-generated Create action).
    ->excludeResources([AuditLogResource::class])

    // Register actions scoped to this panel (on top of the global registry).
    ->action(
        SpotlightAction::make('log-out')
            ->label('Log out')
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->keywords(['signout', 'quit'])
            ->group('Account')
            ->url(fn () => filament()->getLogoutUrl()),
    )
    ->actions([
        SpotlightAction::make('impersonate')->label('Impersonate user')->url('/impersonate'),
    ])

    // Hide actions registered in the global registry by name. Plugin-scoped
    // actions with the same name automatically override their global twin,
    // so overrideActions() is only needed when you want to hide without
    // replacing.
    ->overrideActions(['legacy-action'])

    // Skip the auto-generated "Create {Resource}" entries entirely.
    ->disableCreateActions()

    // Remove Filament's in-topbar global search input in favor of the overlay.
    ->disableDefaultGlobalSearch();
```

### Config file

```php
return [
    'key_binding' => 'mod+k',

    'result_limit_per_category' => 8,

    'excluded_resources' => [],

    'disable_default_global_search' => false,

    'categories' => [
        'records' => true,
        'resources' => true,
        'pages' => true,
        'actions' => true,
    ],

    'disable_create_actions' => false,

    'placeholder' => 'Search…',

    'max_width' => '42rem',

    'override_actions' => [],
];
```

## Actions

### Fluent `SpotlightAction`

```php
use Wezlo\FilamentSearchSpotlight\Actions\SpotlightAction;

SpotlightAction::make('log-out')
    ->label('Log out')
    ->icon('heroicon-o-arrow-right-on-rectangle')
    ->keywords(['signout', 'quit'])
    ->group('Account')
    ->url(fn () => filament()->getLogoutUrl());
```

### Global registry

Call `->register()` to add an action to the app-wide registry (available to
every panel using the plugin). Typically done in `AppServiceProvider::boot()`:

```php
public function boot(): void
{
    SpotlightAction::make('clear-cache')
        ->label('Clear application cache')
        ->keywords(['flush', 'cache'])
        ->url(fn () => route('admin.cache.clear'))
        ->register();
}
```

### Plugin-scoped actions

Pass actions directly to the plugin when you only want them on a specific
panel, or when you want to override a registry action with the same name:

```php
FilamentSearchSpotlightPlugin::make()
    ->action(SpotlightAction::make('log-out')->label('Custom Log out')->url('/custom-logout'));
```

A plugin-scoped action with the same `name` as a registry action automatically
replaces the registry one in the overlay.

### Auto-discovered "Create X" actions

Any resource exposing a `create` page is automatically surfaced as a
`Create {Label}` action. Disable with `->disableCreateActions()` or the
`disable_create_actions` config value.

## Adding a custom category

Categories are tiny — implement the `Category` contract:

```php
use Wezlo\FilamentSearchSpotlight\Categories\Category;
use Wezlo\FilamentSearchSpotlight\Data\SpotlightResult;

class SettingsCategory implements Category
{
    public function key(): string { return 'settings'; }

    public function label(): string { return 'Settings'; }

    public function search(string $query, int $limit): array
    {
        return collect($this->all())
            ->filter(fn ($item) => str_contains(strtolower($item['label']), strtolower($query)))
            ->take($limit)
            ->map(fn ($item) => new SpotlightResult(
                id: 'settings:'.$item['key'],
                category: 'settings',
                title: $item['label'],
                subtitle: null,
                icon: 'heroicon-o-cog-6-tooth',
                url: $item['url'],
            ))
            ->values()
            ->all();
    }

    protected function all(): array { /* … */ }
}
```

Register it by overriding the default category list:

```php
FilamentSearchSpotlightPlugin::make()
    ->categories([
        \Wezlo\FilamentSearchSpotlight\Categories\RecordsCategory::class,
        \Wezlo\FilamentSearchSpotlight\Categories\ResourcesCategory::class,
        SettingsCategory::class,
    ]);
```

## Recent & pinned

Both lists are owned client-side in `localStorage` under
`spotlight.recent` and `spotlight.pinned`. Activating a result appends it to
recent (deduped, max 10). Nothing is persisted server-side, so there is no
database migration to run and no state to clear on logout.

## Opening Spotlight programmatically

Dispatch the `open-spotlight` browser event to open the overlay from a custom
trigger.

```blade
<button x-on:click="$dispatch('open-spotlight')">
    Search
</button>
```

## Tests

Feature tests live alongside the consumer app under
`tests/Feature/FilamentSearchSpotlight/`:

```bash
php artisan test --compact tests/Feature/FilamentSearchSpotlight
```
