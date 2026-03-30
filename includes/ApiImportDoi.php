<?php

namespace MediaWiki\Extension\MardiImport;

use ApiBase;

class ApiImportDoi extends ApiBase {

    public function execute() {
        if ( !$this->getUser()->isRegistered() ) {
            $this->dieWithError( 'You must be logged in.', 'notloggedin' );
        }

        $params = $this->extractRequestParams();
        $dois = $params['dois'];

        $url = 'http://importer/import/doi_async';
        $req = \MediaWiki\MediaWikiServices::getInstance()
            ->getHttpRequestFactory()
            ->create( $url, [ 'method' => 'POST' ], __METHOD__ );

        $req->setHeader( 'Content-Type', 'application/json' );
        $req->setContent( json_encode( [ 'dois' => $dois ] ) );

        $status = $req->execute();

        if ( !$status->isOK() ) {
            $this->dieWithError( 'Failed to contact importer.', 'importerfailed' );
        }

        $this->getResult()->addValue( null, 'result', $req->getContent() );
    }

    public function getAllowedParams() {
        return [
            'dois' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
            ]
        ];
    }

    public function mustBePosted() {
        return true;
    }

    public function isWriteMode() {
        return true;
    }

    public function needsToken() {
        return 'csrf';
    }
}
