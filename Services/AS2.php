<?php

namespace TechData\AS2SecureBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TechData\AS2SecureBundle\Interfaces\PartnerProvider;
use Symfony\Component\HttpFoundation\Request;
use TechData\AS2SecureBundle\Events\MessageReceived;
use TechData\AS2SecureBundle\Models\Server;
use TechData\AS2SecureBundle\Factories\Request as RequestFactory;

/**
 * Description of AS2
 *
 * @author wpigott
 */
class AS2 {

    CONST EVENT_MESSAGE_RECEIVED = 'message_received';

    /**
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     *
     * @var PartnerProvider 
     */
    private $partnerProvider;

    /**
     * @var Server
     */
    private $as2Server;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    public function __construct(EventDispatcherInterface $eventDispatcher, Server $server, RequestFactory $requestFactory) {
        $this->eventDispatcher = $eventDispatcher;
        $this->as2Server = $server;
        $this->requestFactory = $requestFactory;

        // Define constants which are leveraged by AS2Secure.
        define('AS2_DIR_BIN', $as2DirectoryBin);
    }

    /**
     * @param PartnerProvider $partnerProvider
     */
    public function setPartnerProvider(PartnerProvider $partnerProvider)
    {
        $this->partnerProvider = $partnerProvider;
    }



    public function handleRequest(Request $request) {
        // Convert the symfony request to a as2s request
        $as2Request = $this->requestFactory->build($request->getContent(), new \AS2Header($request->headers->all()));

        // Take the request and lets AS2S handle it
        $as2Response = $this->as2Server->handle($as2Request);

        // Get the partner and verify they are authorized
        $partner = $as2Response->getPartnerFrom();
        // @TODO Authorize the partner.

        // process all EDI-X12 messages contained in the AS2 payload
        $response_object = $as2Response->getObject();
        try {
            // the AS2 payload may be further encoded, try to decode it.
            $response_object->decode();
        } catch (\Exception $e) {
            // there was an exception while attemptiong to decode, so the message was probably not encoded... ignore the exception
        }
        $files = $response_object->getFiles();
        foreach ($files as $file) {
            // We have an incoming message.  Lets fire the event for it.
            $event = new MessageReceived();
            $event->setMessage(file_get_contents($file['path']));
            $this->eventDispatcher->dispatch(MessageReceived::EVENT, $event);
        }
    }

}

/*


// process request
        try {
            // only accept requests via HTTP POST
            if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST')) {

                // handle incoming AS2 message
                $response = AS2Server::handle();

                // query for purchasing VAR's profile information
                $partner_from = $response->getPartnerFrom();
                $var_as2_id = trim($partner_from);
                $query = "	SELECT instance_id, var_xid, edi_incoming_auth, edi_segment_delimiter, edi_element_delimiter
					FROM bih1241 AS var
					WHERE edi_as2_id_prod=$1	";
                $result = eas_query_params($query, array($var_as2_id));
                if ($var = pg_fetch_assoc($result->recordset)) {
                    // set delimiters for use in EDI-X12 997 functional acknowledgement
                    $segment_delimiter = $var['edi_segment_delimiter'];
                    if (!$segment_delimiter) {
                        // a segment delimiter was not set in the purchasing VAR's profile.  default to  ~
                        $segment_delimiter = '~';
                    }
                    $element_delimiter = $var['edi_element_delimiter'];
                    if (!$element_delimiter) {
                        // an element delimiter was not set in the purchasing VAR's profile.  default to  *
                        $element_delimiter = '*';
                    }

                    // initialize the JSON-RPC client with domain and service URI
                    $jsonrpc_client = new JsonRpcClient($_SERVER['SERVER_NAME'], '/rpc/rpc.php?service=EDIIncoming');

                    // define JSON-RPC method, params, and request_id, then dispatch request
                    $method = 'process_incoming';
                    $params['var_id'] = $var['instance_id'];
                    $params['auth'] = $var['edi_incoming_auth'];
                    $request_id = 1;

                    // process all EDI-X12 messages contained in the AS2 payload
                    $response_object = $response->getObject();
                    try {
                        // the AS2 payload may be further encoded, try to decode it.
                        $response_object->decode();
                    } catch (Exception $e) {
                        // there was an exception while attemptiong to decode, so the message was probably not encoded... ignore the exception
                    }
                    $files = $response_object->getFiles();
                    foreach ($files as $key => $file) {
                        // send EDI-X12 message to EDIIncoming JSON-RPC service
                        $params['message'] = file_get_contents($file['path']);
                        
                        } else {
                            // TODO!! there was an error processing the message, send a 997 rejection response
                        }
                    }
                }
            } else {
                // there was no POST data sent with the HTTP request
                header('HTTP/1.1 400 Bad Request', true, 400);
                echo 'To submit an AS2 message to StreamOne, you must POST the message to this URL.';
                exit;
            }
        } catch (Exception $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    public function sendMessage() {
        // process request to build outbound AS2 message to VAR
        if (trim($_REQUEST['message']) != '') {
            // query for requested receiving VAR's profile information
            $query = "	SELECT instance_id, edi_incoming_auth, edi_as2_id_prod, company_name
				FROM bih1241 AS var
				WHERE instance_id=$1	";
            $result = eas_query_params($query, array($_REQUEST['var_id']));
            $var = pg_fetch_assoc($result->recordset);
            if ($var && (trim($_REQUEST['auth']) == trim($var['edi_incoming_auth']))) {
                // initialize outbound AS2Message object
                $params = array('partner_from' => '081940553STM1',
                    'partner_to' => $var['edi_as2_id_prod']);
                $message = new AS2Message(false, $params);

                // initialize AS2Adapter for public key encryption between StreamOne and the receiving VAR
                $adapter = new AS2Adapter('081940553STM1', $var['edi_as2_id_prod']);

                // write the EDI message that will be sent to a temp file, then use the AS2 adapter to encrypt it
                $tmp_file = $adapter->getTempFilename();
                file_put_contents($tmp_file, $_REQUEST['message']);
                $message->addFile($tmp_file, 'application/edi-x12');
                $message->encode();

                // initialize outbound AS2 client
                $client = new AS2Client();

                // send AS2 message
                $result = $client->sendRequest($message);
                $result_text = print_r($result, true);
                @mail('mike.kristopeit@etelos-inc.com', 'AS2/EDI-X12 message sent', "{$_SERVER['SERVER_NAME']}\n\nVAR: {$var['company_name']} (ID: {$var['instance_id']}, XID: {$var['var_xid']})\n\nmessage: \n{$_REQUEST['message']}\n\nresponse: \n$result_text");
                echo 'sent';
            } else {
                // bad request - either the requested receiving VAR does not exist, or an invalid auth code was provided
                header('HTTP/1.1 400 Bad Request', true, 400);
                echo 'Bad Request';
                exit;
            }
        } else {
            // bad request - blank message
            header('HTTP/1.1 400 Bad Request', true, 400);
            echo 'Bad Request';
            exit;
        }
    }
 *
