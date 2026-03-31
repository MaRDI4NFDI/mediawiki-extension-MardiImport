<?php

namespace MediaWiki\Extension\MardiImport;

use ApiBase;

class ApiUpdateWikidata extends ApiBase {

    public function execute() {
        $logger = \MediaWiki\Logger\LoggerFactory::getInstance( 'MardiImport' );

        // Block anonymous users — CSRF token requirement enforces this automatically,
        // but we add an explicit check for clarity.
        if ( !$this->getUser()->isRegistered() ) {
            $this->dieWithError( 'You must be logged in.', 'notloggedin' );
        }

        $params = $this->extractRequestParams();
        $qid = $params['qid'];

        // Call internal importer server-side via POST — never exposed to the browser
        $baseUrl = \MediaWiki\MediaWikiServices::getInstance()->getMainConfig()->get( 'MardiImportBaseUrl' );
        $url = $baseUrl . '/update/wikidata';
        $logger->info( 'updateItemFromWikiData called', [ 'user' => $this->getUser()->getName(), 'qid' => $qid ] );

        $req = \MediaWiki\MediaWikiServices::getInstance()
            ->getHttpRequestFactory()
            ->create( $url, [ 'method' => 'POST', 'postData' => json_encode( [ 'qids' => [ $qid ] ] ) ], __METHOD__ );

        $req->setHeader( 'Content-Type', 'application/json' );

        $status = $req->execute();

        if ( !$status->isOK() ) {
            $logger->error( 'Failed to contact importer', [ 'url' => $url ] );
            $this->dieWithError( 'Failed to contact importer.', 'importerfailed' );
        }

        $logger->info( 'updateItemFromWikiData succeeded', [ 'qid' => $qid ] );
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