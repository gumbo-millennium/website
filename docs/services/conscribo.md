# Conscribo Connector Service

Since Gumbo uses Conscribo for their administration, we're connecting with the
Conscribo API. This is a somewhat tricky API since it's a stateful API and we
need to keep track of sessions.

## Terminology

In Conscribo, there are no such things as resources. Members, committees and
other entities are all called "relations" since they have a relation to the
student community.

## Endpoint

Conscribo uses a single endpoint for all API traffic. This endpoint
needs to be called with an `x-conscribo-api-version` header. If you're
not making an authentication request, you also need to include the `x-conscribo-sessionid`
with the ID of the session given by the authentication route.

All requests should be POST requests with an `application/json` content-type,
and since we're requesting JSON responses, we're also sending an `Accept: application/json` header.

The endpoint contains a variable: `account`, which is the name of the account to connect with.

```text
https://secure.conscribo.nl/{account}/request.json
```

## Message body

The message body of the request is a JSON with a `command` key and
a number of extra fields that command uses. For example, an authentication request
requires a `userName` and `passPhrase`, so we combine that with the `command` to get
the following body:

```json
{
    "request": {
        "command": "authenticateWithUserAndPass",
        "userName": "my-username",
        "passPhrase": "hunter02"
    }
}
```

## Responses

All responses are answered in JSON and the HTTP response
code will always be 200, even if the request failed (unless
a system is down, but those are 500+ statuses).

All responses will be contained in a `result` property, including
the `success` key. It's essential to look at `result→success` to
determine if the request was succesful or not, and to report
back any failures.

### Succesful responses

If `success = 1`, the request was successful and you can use the
rest of the response.

```jsonc
{
    "result": {
        "success": 1,
        // More fields...
    }
}
```

### Failed responses

If `success = 0`, the request failed. The `notification` array will contain the reason(s)
the request failed.

```json
{
    "result": {
        "success": 0,
        "notifications": {
            "notification": [
                "Example error",
            ]
        }
    }
}
```

> {note} If the request failed due to an invalid session ID, the
> `ConscriboService` will re-authenticate and retry the request
> exactly once.

## Authentication

To connect to the Conscribo API, we need to obtain a Session ID first. This is
done using the above mentioned `authenticateWithUserAndPass` command.

The response of this request, if `success` is `1`, will contain a `sessionId`
that is valid for approximately 30 minutes, but may be revoked at any time
(only one session is allowed per account, so if a different device logs in with
the same credentials, the previous session will be terminated).

> {note} Sessions may be revoked at any time. The ConscriboService tries to
> re-establish the session automatically, but might fail and throw a fit at
> random.

## Making arbitrary requests

The simplest approach is one that performs a single request and returns the
response. This is done using the `request` method on the service.

```php
Conscribo::request('listArbitraryThing', [
    'thing' => 'value',
]);
```

The API will take care of authenticating in case no session exists, and re-authenticating
in case the session was expired. If anything goes wrong, a ConscriboException is thrown with
a specific error code.

To prevent magic numbers, you may use the `ConscriboErrorCodes` enum, and the specific value returned by the
`ConscriboException::getConscriboCode()` method.

> {warning} Don't use this method to authenticate manually.
> The authentication process is completely handled by the service.

## Requesting relations

To request relations, you can use the `query` method on the service.
This query takes a single parameter: the type of the resource.

This will then return a QueryBuilder instance that you can use to
define constraints and query the results.

```php
$sponsors = Conscribo::query('sponsor')
    ->where('naam', 'Conscribo')
    ->get();
```

If you omit any where-filter, the query will return all results in a single response.

### Requesting users

Requesting users is similar to a normal relationship request, but since you'll pre-define
the type in your `config/conscribo.php` file, you may instead use the `userQuery()` method.

The rest of the flow is identical to the normal query method.

## Requesting groups

The groups system of Conscribo is a tad weird. You can only requests all groups and their members,
or just a single one. No other APIs concerning groups exist. You may also not add filters, so there's
no chainig required.

```php
$allGroups = Conscribo::listGroups();

$singleGroup = Conscribo::getGroup(24);
```
