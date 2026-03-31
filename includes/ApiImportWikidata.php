<?php

namespace MediaWiki\Extension\MardiImport;

use ApiBase;

class ApiImportWikidata extends ApiBase {

    public function execute() {
        $logger = \MediaWiki\Logger\LoggerFactory::getInstance( 'MardiImport' );

        if ( !$this->getUser()->isRegistered() ) {
            $this->dieWithError( 'You must be logged in.', 'notloggedin' );
        }

        $params = $this->extractRequestParams();
        $qids = $params['qids'];

        $url = 'http://importer/import/wikidata';
        $logger->info( 'importItemFromWikiData called', [ 'user' => $this->getUser()->getName(), 'qids' => $qids ] );

        $req = \MediaWiki\MediaWikiServices::getInstance()
            ->getHttpRequestFactory()
            ->create( $url, [ 'method' => 'POST' ], __METHOD__ );

        $req->setHeader( 'Content-Type', 'application/json' );
        $req->setContent( json_encode( [ 'qids' => $qids ] ) );

        $status = $req->execute();

        if ( !$status->isOK() ) {
            $logger->error( 'Failed to contact importer', [ 'url' => $url ] );
            $this->dieWithError( 'Failed to contact importer.', 'importerfailed' );
        }

        $logger->info( 'importItemFromWikiData succeeded', [ 'qids' => $qids ] );
        $this->getResult()->addValue( null, 'result', $req->getContent() );
    }

    public function getAllowedParams() {
        return [
            'qids' => [
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
