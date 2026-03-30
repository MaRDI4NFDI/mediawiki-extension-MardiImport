<?php

namespace MediaWiki\Extension\MardiImport;

use ApiBase;

class ApiUpdateWikidata extends ApiBase {

    public function execute() {
        // Block anonymous users — CSRF token requirement enforces this automatically,
        // but we add an explicit check for clarity.
        if ( !$this->getUser()->isRegistered() ) {
            $this->dieWithError( 'You must be logged in.', 'notloggedin' );
        }

        $params = $this->extractRequestParams();
        $qid = $params['qid'];

        // Call internal importer server-side via POST — never exposed to the browser
        $url = 'http://importer/update/wikidata';
        $req = \MediaWiki\MediaWikiServices::getInstance()
            ->getHttpRequestFactory()
            ->create( $url, [ 'method' => 'POST' ], __METHOD__ );

        $req->setHeader( 'Content-Type', 'application/json' );
        $req->setContent( json_encode( [ 'qids' => [ $qid ] ] ) );

        $status = $req->execute();

        if ( !$status->isOK() ) {
            $this->dieWithError( 'Failed to contact importer.', 'importerfailed' );
        }

        $this->getResult()->addValue( null, 'result', $req->getContent() );
    }

    public function getAllowedParams() {
        return [
            'qid' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
            ]
        ];
    }

    // Require POST requests — prevents GET-based abuse
    public function mustBePosted() {
        return true;
    }

    // Marks this as a write operation — enforces CSRF validation
    public function isWriteMode() {
        return true;
    }

    // Require a valid CSRF token — anonymous users cannot obtain one
    public function needsToken() {
        return 'csrf';
    }
}