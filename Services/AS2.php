<?php

namespace TechData\AS2SecureBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use TechData\AS2SecureBundle\Events\Log;
use TechData\AS2SecureBundle\Events\MessageReceived;
use TechData\AS2SecureBundle\Events\MessageSent;
use TechData\AS2SecureBundle\Factories\Adapter as AdapterFactory;
use TechData\AS2SecureBundle\Models\Client;
use TechData\AS2SecureBundle\Factories\Message as MessageFactory;
use TechData\AS2SecureBundle\Factories\Partner as PartnerFactory;
use TechData\AS2SecureBundle\Factories\Request as RequestFactory;
use TechData\AS2SecureBundle\Interfaces\MessageSender;
use TechData\AS2SecureBundle\Models\Header;
use TechData\AS2SecureBundle\Models\Server;

/**
 * Description of AS2
 *
 * @author wpigott
 */
class AS2 implements MessageSender
{
    
    /**
     *
     */
    CONST EVENT_MESSAGE_RECEIVED = 'message_received';
    
    /**
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    /**
     *
     * @var PartnerFactory
     */
    private $partnerFactory;
    
    /**
     * @var Server
     */
    private $as2Server;
    
    /**
     * @var RequestFactory
     */
    private $requestFactory;
    /**
     * @var MessageFactory
     */
    private $messageFactory;
    /**
     * @var AdapterFactory
     */
    private $adapterFactory;
    /**
     * @var Client
     */
    private $client;
    
    /**
     * AS2 constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Server                   $server
     * @param RequestFactory           $requestFactory
     * @param PartnerFactory           $partnerFactory
     * @param MessageFactory           $messageFactory
     * @param AdapterFactory           $adapterFactory
     * @param Client                   $client
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Server $server, RequestFactory $requestFactory, PartnerFactory $partnerFactory, MessageFactory $messageFactory, AdapterFactory $adapterFactory, Client $client)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->as2Server       = $server;
        $this->requestFactory  = $requestFactory;
        $this->partnerFactory  = $partnerFactory;
        $this->messageFactory  = $messageFactory;
        $this->adapterFactory  = $adapterFactory;
        $this->client          = $client;
    }
    
    /**
     * @param Request $request
     */
    public function handleRequest(Request $request)
    {
        // Convert the symfony request to a as2s request
        $as2Request = $this->requestToAS2Request($request);
        // Take the request and lets AS2S handle it
        // Get the partner and verify they are authorized
        
        // @TODO Authorize the partner.
        // process all EDI-X12 messages contained in the AS2 payload
        
        
        $as2Response = $this->as2Server->handle($as2Request);
        
        try {
            
            $partner         = $as2Response->getPartnerFrom();
            $response_object = $as2Response->getObject();
            // the AS2 payload may be further encoded, try to decode it.
            
            $response_object->decode();
        } catch (\Exception $e) {
            $this->eventDispatcher->dispatch(Log::EVENT, new Log(Log::TYPE_ERROR, $e->getMessage()));
        }
        $files = $response_object->getFiles();
        foreach ($files as $file) {
            // We have an incoming message.  Lets fire the event for it.
            $event = (new MessageReceived())->setMessageId($as2Response->getMessageId())->setMessage(file_get_contents($file['path']))->setType(MessageReceived::TYPE_MESSAGE)->setSendingPartnerId($partner->id)->setReceivingPartnerId($as2Response->getPartnerTo()->id);
            
            $this->eventDispatcher->dispatch(MessageReceived::EVENT, $event);
        }
    }
    
    /**
     * @param Request $request
     *
     * @return \TechData\AS2SecureBundle\Models\Request
     */
    private function requestToAS2Request(Request $request)
    {
        $flattenedHeaders = [];
        foreach ($request->headers as $key => $header) {
            $flattenedHeaders[$key] = reset($header);
        }
        
        return $this->requestFactory->build($request->getContent(), new Header($flattenedHeaders));
    }
    
    /**
     * @param       $toPartner
     * @param       $fromPartner
     * @param string $messageContent
     * @param string $mimeType
     * @param string $encoding
     *
     * @return  array
     * @throws \Exception
     * @throws \TechData\AS2SecureBundle\Models\AS2Exception
     * @throws \TechData\AS2SecureBundle\Models\Exception
     */
    public function sendMessage($toPartner, $fromPartner, $messageContent,$mimeType = "application/xml")
    {
        // process request to build outbound AS2 message to VAR
        
        // initialize outbound AS2Message object
        $message = $this->messageFactory->build(false, [
            'partner_from' => $fromPartner,
            'partner_to'   => $toPartner,
        ]);
        
        // initialize AS2Adapter for public key encryption between StreamOne and the receiving VAR
        $adapter = $this->adapterFactory->build($fromPartner, $toPartner);
        
        // write the EDI message that will be sent to a temp file, then use the AS2 adapter to encrypt it
        $message->addFile($messageContent, $mimeType, "", false);
        $message->encode();
        
        // send AS2 message
        $result = $this->client->sendRequest($message);
        $this->eventDispatcher->dispatch(Log::EVENT, new Log(Log::TYPE_INFO, "Log sending Message: " . $result['log']));
        $messageSent = new MessageSent();
        $messageSent->setCode($result['info']['http_code']);
        $messageSent->setMessageId($message->getMessageId());
        $messageSent->setType(MessageSent::TYPE_MESSAGE);
        $messageSent->setMessage($messageContent);
        $this->eventDispatcher->dispatch(MessageSent::EVENT, $messageSent);
        
        return $result;
    }
}

