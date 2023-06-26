+++
date = "2023-06-26"
draft = false
weight = 10
description = "To make it easy to use Sphinx."
title = "Facade"
bref= "There is a facade class to make working easier. this facade contains several methods that we will introduce in continue."
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

### generateTokenFor

Generates access and refresh token for the given user.

### getAccessToken

Returns generated access token.

### getRefreshToken

Returns generated refresh token.

### claim

Add a custom claim to the token.

### header

Add a custom header to the token.

### validateWrapperAccessToken

Validate wrapper access token of the given token.

### assertWrapperAccessToken

Assert wrapper access token of the given token.

### validateInnerAccessToken

Validate inner access token of the given token.

### assertInnerAccessToken

Assert inner access token of the given token.

### getInnerAccessToken

Return inner access token of the given token.

### validateWrapperRefreshToken

Validate wrapper refresh token of the given token.

### assertWrapperRefreshToken

Assert wrapper refresh token of the given token.

### validateInnerRefreshToken

Validate inner refresh token of the given token.

### assertInnerRefreshToken

Assert inner refresh token of the given token.

### getInnerRefreshToken

Return inner refresh token of the given token.

### getPermissions

Get permissions of the given token.

### isRefreshToken

Determine the token is refresh token or not.

### isNotRefreshToken

Determine the token is not a refresh token.

### getCurrentSession

Return current selected session.