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

## Requesting relations

To request relations, you can use the `relations` method on the service.
This helper contains methods to retrieve all members and committes, but
you can also call the `getTypes()` method to get a list of relation
types and request arbitrary relations with the `getRelation(string $relation)` method.

Internally, the relations helper takes care of retrieving the fields and caching them,
and requesting the relation with all known fields.

### Filtering results

Since filters are somewhat complex, we recommend you use the `Filter` helper
class, which has method like `where` and `whereIn` and gets mapped to
fields that look very similar.

Please note that the API will throw exceptions if it cannot resolve the filters
for a given class.

```php
$filters = Filter::make()
    ->where('voornaam', 'John')
    ->whereNot('achternaam', 'Doe')
    ->whereDateBetween('geboortedatum', Date::now(), Date::now()->addWeek());
```


### Requesting users

The most commonly used feature is the ability to request membes.

A user, often called a `persoon` in "API speak" describes a (former) member
of the student community. Most fields are variable, but the `relations()`

## Requesting committees

## Requesting groups
