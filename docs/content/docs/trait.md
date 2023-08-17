+++
date = "2023-06-26"
draft = false
weight = 20
description = "A bunch of useful and handy methods."
title = "Trait"
bref= "Sphinx adds some useful and necessary methods through the trait. there is the list of available methods"
toc = false
+++

## Available methods

{{< rawhtml >}}
<div class="methods-container">

<div class="method">
<a href="#can">can</a>
</div>

<div class="method">
<a href="#canany">canAny</a>
</div>

<div class="method">
<a href="#cant">cant</a>
</div>

<div class="method">
<a href="#cannot">cannot</a>
</div>

<div class="method">
<a href="#getdevicelimit">getDeviceLimit</a>
</div>

<div class="method">
<a href="#extract">extract</a>
</div>

<div class="method">
<a href="#username">username</a>
</div>

<div class="method">
<a href="#extractrole">extractRole</a>
</div>

<div class="method">
<a href="#extractpermissions">extractPermissions</a>
</div>

</div>
{{< /rawhtml >}}

### can

Determine if the entity has the given abilities.

```
$user->can('has-ability'); // ture
$user->can('hasnt-ability'); // false
```

### canAny

Determine if the entity has any of the given abilities.

```
$user->canAny( ['has-ability','hasnt-ability'] ); // true
$user->canAny( ['hasnt-ability','hasnt-other-ability'] ); // false
```

### cant

Determine if the entity does not have the given abilities.

```
$user->cant('hasnt-ability'); // true
$user->cant('has-ability'); // false
```

### cannot

Alias for `cant` method.

### getDeviceLimit

Determine the limitation of logged-in devices using one user account.

```
$user->getDeviceLimit(); // int number
```

### extract

Extract some necessary attributes of user to put in the tokens.

```
$user->extract(); // an array of user's attributes
```

### username

Returns username column name of the user.

```
$user->username(); // email column for example
```

### extractRole

Extract attributes of user's role.

```
$user->extractRole(); // an array of user's role attributes
```

### extractPermissions

Extract attributes of owned permissions of user.

```
$user->extractPermissions(); // an array of user's permissions attributes
```
