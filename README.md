# MardiImport — MediaWiki Extension

A minimal MediaWiki API extension for the MaRDI Portal that allows logged-in users to trigger imports and updates through a link on a page.

## Overview

The MaRDI Portal has items connected to external sources (Wikidata, DOI, …). This extension provides secure server-side API endpoints that verify the requesting user is logged in (via CSRF token) and forward the request to the internal MaRDI importer Flask API service.

The link is rendered client-side and only visible to logged-in users on the respective pages (e.g. person).

## How It Works

```
Browser (logged-in user)
    │
    │  POST /w/api.php?action=<action>&<param>=<id> + CSRF token
    ▼
MediaWiki API (server-side)
    │
    │  Validates CSRF token → rejects anonymous users
    │
    │  POST to internal Flask API
    │
    ▼
Internal MaRDI importer service
```

## Security

- **Login required**: The extension uses `needsToken() = 'csrf'`, which means MediaWiki automatically rejects requests from anonymous users with a `badtoken` error.
- **Internal URL never exposed**: The browser never sees the service URL.

## Installation

This extension is used in the [MaRDI docker-wikibase](https://github.com/MaRDI4NFDI/docker-wikibase) image. It is cloned automatically during the Docker build (`clone_all.sh`).

## Configuration

The base URL of the importer service can be set in `LocalSettings.php`. The default is:

```php
$wgMardiImportBaseUrl = 'http://importer';
```

Override this per environment as needed, e.g. in `LocalSettings.d/staging/mardiimport.php`:

```php
$wgMardiImportBaseUrl = 'http://staging-importer';
```

## Usage

| Action | Param | Flask endpoint |
|---|---|---|
| `updateItemFromWikiData` | `qid` | `/update/wikidata` |
| `importItemFromWikiData` | `qids` | `/import/wikidata` |
| `importItemFromDoi` | `dois` | `/import/doi_async` |

```js
new mw.Api().postWithToken( 'csrf', {
    action: 'updateItemFromWikiData',
    qid: 'Q12345'
} ).done( function ( response ) {
    console.log( 'Success:', response );
} ).fail( function ( error ) {
    console.log( 'Error:', error );
} );
```
