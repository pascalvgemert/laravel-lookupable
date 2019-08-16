# Laravel Lookupable
Lookupable Trait for Laravel eloquent models for quick and efficient table lookups.

[![Total Downloads](https://poser.pugx.org/pascalvgemert/laravel-lookupable/downloads)](https://packagist.org/packages/pascalvgemert/laravel-lookupable)
[![License](https://poser.pugx.org/pascalvgemert/laravel-lookupable/license)](https://packagist.org/packages/pascalvgemert/laravel-lookupable)

## Installation

You can install the package via composer:

``` bash
composer require pascalvgemert/laravel-lookup
```

## Usage

The `Lookupable` trait can be used only for Eloquent Models

```php
class Role extends \Illuminate\Database\Eloquent\Model
{
    use Lookupable/Lookupable;
}
```

### Example table scheme for 'Roles'

id | identifier | title
--- | --- | ---
1 | admin | Admin User
2 | user | System User
3 | guest | Guest User

### Lookupable methods

After this you can lookup instances easily with the following methods:

#### Single Lookup 

```
/** @var \App\Models\Role|null **/
$role = Role::lookup('admin');
```

#### Single Lookup which throws an \Illuminate\Database\Eloquent\ModelNotFoundException error when no record could be found

```
/** @var \App\Models\Role **/
$role = Role::lookupOrFail('admin');
```

#### Multiple Lookup 

```
/** @var \Illuminate\Database\Eloquent\Collection **/
$roles = Role::lookupMany(['admin', 'guest']);
```

#### Multiple Lookup which throws an \Illuminate\Database\Eloquent\ModelNotFoundException error when any of the given record could NOT be found

```
/** @var \Illuminate\Database\Eloquent\Collection **/
$roles = Role::lookupManyOrFail(['admin', 'guest']);
```

### Soft Deleted items

All of the above methods can except a second parameter (`bool $withTrashed = false;`), to also return with Soft Deleted items. 

> Note: When trying to use the Soft Deleted items, please make sure your Eloquent Model implements the \Illuminate\Database\Eloquent\SoftDeletes trait.

### I don't have an `identifier` column?

So your database table doesn't contain an `identifier` column and looks like this `Country` table for example:

id | code | title
--- | --- | ---
1 | NL | Netherlands
2 | UK | Great Brittain
3 | BE | Belgium
4 | US | United States
5 | ... | ...

No worries, you can define your own lookup column name in your model like so:

```php
class Country extends \Illuminate\Database\Eloquent\Model
{
    use Lookupable/Lookupable;
    
    protected $lookupColumn = 'code';
}
```

## Credits

- [Pascal van Gemert](https://github.com/pascalvgemert)
- [Kevin op den Kamp](https://github.com/papa-smurf) (for inspiration)
- [Startselect](https://startselect.com) 
