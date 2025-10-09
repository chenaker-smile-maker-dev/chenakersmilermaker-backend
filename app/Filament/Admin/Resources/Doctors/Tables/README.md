# DoctorsTable Layout Guide

## 🎨 Switching Between Layouts

The `DoctorsTable` class now supports **two different layout modes**:

### 1. **Grid Layout** (Default) 📱

-   Displays doctors in a responsive card-based grid
-   Mobile: 1 column
-   Medium screens (md): 2 columns
-   Extra large screens (xl): 3 columns
-   Features collapsible panels for additional info
-   Beautiful spacing and visual hierarchy

### 2. **Traditional Table Layout** 📊

-   Classic table with rows and columns
-   Toggleable columns
-   Horizontal scrolling on mobile
-   Familiar data table experience

---

## ⚙️ How to Switch Layouts

Open `DoctorsTable.php` and change the `$layout` property:

```php
class DoctorsTable
{
    // For Grid Layout (cards):
    private static string $layout = 'grid';

    // For Table Layout (traditional):
    // private static string $layout = 'table';
}
```

Simply change `'grid'` to `'table'` or vice versa, then refresh your browser!

---

## 🎛️ Customization Options

### Grid Layout Customization

Modify the grid breakpoints in the `configure()` method:

```php
->contentGrid([
    'sm' => 1,   // 1 column on small screens
    'md' => 2,   // 2 columns on medium screens
    'lg' => 3,   // 3 columns on large screens
    'xl' => 4,   // 4 columns on extra large screens
    '2xl' => 5,  // 5 columns on 2xl screens
])
```

### Customizing Grid Layout Columns

Edit the `getGridLayoutColumns()` method to:

-   Add/remove fields
-   Change icons (heroicon-m-\*)
-   Modify colors (success, warning, danger, info, primary, etc.)
-   Adjust spacing with `->space(1)`, `->space(2)`, `->space(3)`
-   Control responsive visibility with `->visibleFrom('md')` or `->hiddenFrom('lg')`

### Customizing Table Layout Columns

Edit the `getTableLayoutColumns()` method to:

-   Add/remove columns
-   Control default visibility with `->toggleable(isToggledHiddenByDefault: true)`
-   Change column order
-   Modify formatting

---

## 📝 Quick Examples

### Adding a New Field to Grid Layout

```php
Stack::make([
    TextColumn::make("name")
        ->weight(FontWeight::Bold)
        ->searchable()
        ->sortable(),

    // Add your new field here:
    TextColumn::make("phone")
        ->icon('heroicon-m-phone')
        ->color('info'),

    TextColumn::make("specialty")
        ->color('gray')
        ->limit(50)
        ->wrap(),
])->space(1),
```

### Adding a New Column to Table Layout

```php
return [
    ImageColumn::make('thumb_image')
        ->toggleable()
        ->circular(),

    TextColumn::make("name")
        ->searchable()
        ->sortable()
        ->toggleable(),

    // Add your new column here:
    TextColumn::make("phone")
        ->searchable()
        ->toggleable(),

    // ... rest of columns
];
```

---

## 🔄 Dynamic Layout Switching (Advanced)

Want to let users choose their preferred layout? You could:

1. Store preference in user settings
2. Use a session variable
3. Add a toggle button in the UI

Example with session:

```php
private static string $layout = null;

public static function configure(Table $table): Table
{
    self::$layout = session('doctors_table_layout', 'grid');

    return $table
        // ... rest of configuration
}
```

---

## 📚 Filament Documentation

For more layout options, visit:

-   [Filament Tables - Layout](https://filamentphp.com/docs/tables/layout)
-   [Responsive breakpoints](https://tailwindcss.com/docs/responsive-design)

---

**Created**: October 9, 2025  
**Version**: 1.0
