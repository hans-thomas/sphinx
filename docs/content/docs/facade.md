+++
date = "2023-06-26"
draft = false
weight = 10
description = "To make it easy to use Sphinx."
title = "Facade"
bref= "There is a facade class to make working easier. this facade contains several methods that we will introduce in continue"
toc = false
+++


## Available methods

{{< rawhtml >}}
<div class="methods-container">

<div class="method">
<a href="#decode">decode</a>
</div>

<div class="method">
<a href="#generatetokenfor">generateTokenFor</a>
</div>

<div class="method">
<a href="#getaccesstoken">getAccessToken</a>
</div>

<div class="method">
<a href="#claim">claim</a>
</div>

<div class="method">
<a href="#header">header</a>
</div>

<div class="method">
<a href="#validatewrapperaccesstoken">validateWrapperAccessToken</a>
</div>

<div class="method">
<a href="#assertwrapperaccesstoken">assertWrapperAccessToken</a>
</div>

<div class="method">
<a href="#validateinneraccesstoken">validateInnerAccessToken</a>
</div>

<div class="method">
<a href="#assertinneraccesstoken">assertInnerAccessToken</a>
</div>

<div class="method">
<a href="#getinneraccesstoken">getInnerAccessToken</a>
</div>

<div class="method">
<a href="#validatewrapperrefreshtoken">validateWrapperRefreshToken</a>
</div>

<div class="method">
<a href="#assertwrapperrefreshtoken">assertWrapperRefreshToken</a>
</div>

<div class="method">
<a href="#validateinnerrefreshtoken">validateInnerRefreshToken</a>
</div>

<div class="method">
<a href="#assertinnerrefreshtoken">assertInnerRefreshToken</a>
</div>

<div class="method">
<a href="#getinnerrefreshtoken">getInnerRefreshToken</a>
</div>

<div class="method">
<a href="#getpermissions">getPermissions</a>
</div>

</div>
{{< /rawhtml >}}

### decode

Decodes a jwt token.

```
Sphinx::decode($token);
```

### generateTokenFor

Generates access and refresh token for the given user.

```
Sphinx::generateTokenFor($user);
```

### getAccessToken

Returns generated access token.

```
Sphinx::generateTokenFor($user)->getAccessToken();
```

### getRefreshToken

Returns generated refresh token.

```
Sphinx::generateTokenFor($user)->getRefreshToken();
```

### claim

Adds a custom claim to the access token.

```
Sphinx::generateTokenFor($user)
       ->claim('new', 'test')
       ->getAccessToken();
```

### header

Adds a custom header to the access token.

```
Sphinx::generateTokenFor($user)
       ->header('new', 'test')
       ->getAccessToken();
```

### validateWrapperAccessToken

Validates wrapper access token of the given token and returns `bool` value.

```
Sphinx::validateWrapperAccessToken($token);
```

### assertWrapperAccessToken

Asserts wrapper access token of the given token and throws an exception if token is not valid.

```
Sphinx::assertWrapperAccessToken($token);
```

### validateInnerAccessToken

Validates inner access token of the given token.

```
Sphinx::validateInnerAccessToken($token);
```

### assertInnerAccessToken

Asserts inner access token of the given token.

```
Sphinx::assertWrapperAccessToken($token);
```

### getInnerAccessToken

Returns inner access token of the given token.

```
Sphinx::getInnerAccessToken($token);
```

### validateWrapperRefreshToken

Validates wrapper refresh token of the given token and returns a `bool` value.

```
Sphinx::validateWrapperRefreshToken($token);
```

### assertWrapperRefreshToken

Asserts wrapper refresh token of the given token and throws an exception if token is not valid.

```
Sphinx::assertWrapperRefreshToken($token);
```

### validateInnerRefreshToken

Validates inner refresh token of the given refresh token.

```
Sphinx::validateInnerRefreshToken($token);
```

### assertInnerRefreshToken

Asserts inner refresh token of the given token.

```
Sphinx::assertInnerRefreshToken($token);
```

### getInnerRefreshToken

Returns inner refresh token of the given token.

```
Sphinx::getInnerRefreshToken($token);
```

### getPermissions

Returns permissions of the given token.

```
Sphinx::getPermissions($token);
```

### isRefreshToken

Determines the token is refresh token or not.

```
Sphinx::generateTokenFor($user);
$refresh = Sphinx::getRefreshToken();
$access = Sphinx::getAccessToken();

Sphinx::isRefreshToken($refresh); // true
Sphinx::isRefreshToken($access); // false
```

### isNotRefreshToken

Determines the token is not a refresh token.

```
Sphinx::generateTokenFor($user);
$access = Sphinx::getAccessToken();
$refresh = Sphinx::getRefreshToken();

Sphinx::isNotRefreshToken($access); // ture
Sphinx::isNotRefreshToken($refresh); // false
```

### getCurrentSession

Returns current selected session that the inner token encrypted with.

```
Sphinx::getCurrentSession();
```