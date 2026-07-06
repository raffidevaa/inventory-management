# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Retrieve a token by sending your credentials to <code>POST /api/v1/auth/login</code>, then send it as a <code>Bearer</code> token in the <code>Authorization</code> header.
