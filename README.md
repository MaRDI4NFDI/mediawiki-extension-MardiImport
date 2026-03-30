# MardiImport — MediaWiki Extension

A minimal MediaWiki API extension for the MaRDI Portal that allows logged-in users to trigger an import/ update through a link on a page.

## Overview

The MaRDI Portal has items that are connected to WikiData item. This extension provides a secure server-side API endpoint (`action=updateItemFromWikiData`) that:

- Accepts a Wikidata QID as input
- Verifies the requesting user is logged in (via CSRF token)
- Forwards the request to the internal MaRDI importer Flask API service (which will then trigger the update)

The link is rendered client-side and only visible to logged-in users on the respective pages (e.g. person).

## How It Works

```
Browser (logged-in user)
    │
    │  POST /w/api.php?action=updateItemFromWikiData&qid=Q12345 + CSRF token
    ▼
MediaWiki API (server-side)
    │
    │  Validates CSRF token → rejects anonymous users
    │
    │  POST to internal Flask API with QID
    │       
    ▼
Internal MaRDI importer service triggers update
```

## Security

- **Login required**: The extension uses `needsToken() = 'csrf'`, which means MediaWiki automatically rejects requests from anonymous users with a `badtoken` error. 
- **Internal URL never exposed**: The browser never sees the service URL.

## Installation

This extension is using in the [MaRDI docker-wikibase](https://github.com/MaRDI4NFDI/docker-wikibase) image. It is cloned automatically during the Docker build (`clone_all.sh`).

## Usage

The extension is called using JS:
```
mw.Api().postWithToken( 'csrf', {
    action: 'updateItemFromWikiData',
    qid: 'Q12345'
} ... )
```
