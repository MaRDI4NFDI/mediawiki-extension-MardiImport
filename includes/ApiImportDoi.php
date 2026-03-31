<?php

namespace MediaWiki\Extension\MardiImport;

use ApiBase;

class ApiImportDoi extends ApiBase {

    public function execute() {
        $logger = \MediaWiki\Logger\LoggerFactory::getInstance( 'MardiImport' );

        if ( !$this->getUser()->isRegistered() ) {
            $this->dieWithError( 'You must be logged in.', 'notloggedin' );
        }

        $params = $this->extractRequestParams();
        $dois = $params['dois'];

        $url = 'http://importer/import/doi_async';
        $logger->info( 'importItemFromDoi called', [ 'user' => $this->getUser()->getName(), 'dois' => $dois ] );

        $req = \MediaWiki\MediaWikiServices::getInstance()
            ->getHttpRequestFactory()
            ->create( $url, [ 'method' => 'POST', 'postData' => json_encode( [ 'dois' => $dois ] ) ], __METHOD__ );

        $req->setHeader( 'Content-Type', 'application/json' );

        $status = $req->execute();

        if ( !$status->isOK() ) {
            $logger->error( 'Failed to contact importer', [ 'url' => $url ] );
            $this->dieWithError( 'Failed to contact importer.', 'importerfailed' );
        }

        $logger->info( 'importItemFromDoi succeeded', [ 'dois' => $dois ] );
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
