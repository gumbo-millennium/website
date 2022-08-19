# Ticketing Barcode API

The Ticketing barcode API is an API to preload and consume barcodes of activity tickets.

The API has two routes:
 - `/api/barcodes/{activity}/preload`: Provides a preload list of barcodes for an activity.
 - `/api/barcodes/{activity}/consume`: Consumes a barcode for an activity.

## Authorization

The API requires a first-party token supplied in the `Authorization` header.
You cannot create a Ticketing Barcode API token yourself.

## Preload

The Preload list contains the first 12 characters of a salted SHA256 hash of the barcode.
You can use this to locally verify if a barcode is going to be invalid.

```http
GET /api/barcodes/{activity}/preload
Authorization: Bearer <token>
Accept: application/json
```

### How to use

1. Make a call to `/api/barcodes/{activity}/preload`
2. Store the `data.salt` and `data.barcodes` response in-memory somewhere.
3. Scan a barcode
4. Transform the barcod to uppercase. and prefix it with the salt.
5. Hash it with SHA256 and check the `barcodes` for the first 12 characters of the hash.
   1. If the hash is in the list, the barcode is likely valid.
   2. If the hash isn't in the list, the barcode is definitely invalid.

> *note*: Consumed barcodes remain in the list, to preserve system security.

If the hashing isn't clear, here is some Javascript:

```js
import sha256 from 'crypto-js/sha256';

const calculateHashPartial( salt, barcode ) {
    return sha256( `${salt}${barcode}`.toUpperCase() ).toString().substring( 0, 12 );
}
```

## Consume

The consume API will consume a barcode for an activity. This is a destructive
action. After a barcode is consumed, the enrollment is no longer transferrable or
eligible for cancellation.

```http
POST /api/barcodes/{activity}/consume
Authorization: Bearer <token>
Content-Type: application/json
Accept: application/json

{
  "barcode": "1234ABCD"
}
```

### How to use

1. Scan a barcode
2. Make a call to `/api/barcodes/{activity}/consume` with the barcode in the body.
   1. If the barcode is valid, a 200 OK response is returned
   2. If the barcode is invalid, a 404 Not Found response is returned
   3. If the barcode has already been consumed, a 409 Conflict response is returned
