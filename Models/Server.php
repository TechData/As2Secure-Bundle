<?php
namespace TechData\AS2SecureBundle\Models;

/**
 * AS2Secure - PHP Lib for AS2 message encoding / decoding
 *
 * @author  Sebastien MALOT <contact@as2secure.com>
 *
 * @copyright Copyright (c) 2010, Sebastien MALOT
 *
 * Last release at : {@link http://www.as2secure.com}
 *
 * This file is part of AS2Secure Project.
 *
 * AS2Secure is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AS2Secure is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AS2Secure.
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html GNU General Public License
 * @version 0.9.0
 *
 */

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TechData\AS2SecureBundle\Events\Log;
use TechData\AS2SecureBundle\Events\MessageSent;

class Server
{
    const TYPE_MESSAGE = 'Message';
    const TYPE_MDN = 'MDN';

    /**
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle a request (server side)
     *
     * @param request (If not set, get data from standard input)
     *
     * @return request    The request handled
     */
    public function handle($request = null)
    {
        // handle any problem in case of SYNC MDN process
        ob_start();

        try {
            $error = null;

            if (!$request instanceof Request) {
                throw new AS2Exception('Unexpected error occurs while handling AS2 message : bad format');
            }

            $headers = $request->getHeaders();


            $object = $request->getObject();
        } catch (Exception $e) {
            // get error while handling request
            $error = $e;
            //throw $e;
        }

        //
        $mdn = null;

        if ($object instanceof Message || (!is_null($error) && !($object instanceof MDN))) {
            $object_type = self::TYPE_MESSAGE;
            $this->eventDispatcher->dispatch('log', new Log(Log::TYPE_INFO, 'Incoming transmission is a Message.'));

            try {
                if (is_null($error)) {
                    $object->decode();
                    $files = $object->getFiles();
                    $this->eventDispatcher->dispatch('log', new Log(Log::TYPE_INFO, count($files) . ' payload(s) found in incoming transmission.'));
                    foreach ($files as $key => $file) {
                        $content = file_get_contents($file['path']);
                        $this->eventDispatcher->dispatch('log', new Log(Log::TYPE_INFO, 'Payload #' . ($key + 1) . ' : ' . round(strlen($content) / 1024, 2) . ' KB / "' . $file['filename'] . '".'));
                        $this->saveMessage($content, array(), 'payload');
                    }

                    $mdn = $object->generateMDN($error);
                    $mdn->encode($object);
                } else {
                    throw $error;
                }
            } catch (Exception $e) {
                $params = array('partner_from' => $headers->getHeader('as2-from'),
                    'partner_to' => $headers->getHeader('as2-to'));
                $mdn = new MDN($e, $params);
                $mdn->setAttribute('original-message-id', $headers->getHeader('message-id'));
                $mdn->encode();
            }
        } elseif ($object instanceof MDN) {
            $object_type = self::TYPE_MDN;
            $this->eventDispatcher->dispatch('log', new Log(Log::TYPE_INFO, 'Incoming transmission is a MDN.'));
        } else {
            $this->eventDispatcher->dispatch('log', new Log(Log::TYPE_ERROR, 'Malformed data.'));
        }

        // build MDN
        if (!is_null($error) && $object_type == self::TYPE_MESSAGE) {
            $params = array('partner_from' => $headers->getHeader('as2-from'),
                'partner_to' => $headers->getHeader('as2-to'));
            $mdn = new MDN($e, $params);
            $mdn->setAttribute('original-message-id', $headers->getHeader('message-id'));
            $mdn->encode();
        }

        // send MDN
        if (!is_null($mdn)) {
            if (!$headers->getHeader('receipt-delivery-option')) {
                // SYNC method

                // re-active output data
                ob_end_clean();

                // send headers
                foreach ($mdn->getHeaders() as $key => $value) {
                    $header = str_replace(array("\r", "\n", "\r\n"), '', $key . ': ' . $value);
                    header($header);
                }

                // output MDN
                echo $mdn->getContent();

                $this->eventDispatcher->dispatch('log', new Log(Log::TYPE_INFO, 'An AS2 MDN has been sent.'));
            } else {
                // ASYNC method

                // cut connection and wait a few seconds
                $this->closeConnectionAndWait(5);

                // delegate the mdn sending to the client
                $client = new Client();
                $result = $client->sendRequest($mdn);
                if ($result['info']['http_code'] == '200') {
                    $this->eventDispatcher->dispatch('log', new Log(Log::TYPE_INFO, 'An AS2 MDN has been sent.'));
                } else {
                    $this->eventDispatcher->dispatch('log', new Log(Log::TYPE_ERROR, 'An error occurs while sending MDN message : ' . $result['info']['http_code']));
                }
            }
        }

        return $request;
    }

    /**
     * Save the content of the request for futur handle and/or backup
     *
     * @param content       The content to save (mandatory)
     * @param headers       The headers to save (optional)
     * @param type          Values : raw | decrypted | payload (mandatory)
     *
     * @return       String  : The main filename
     */
    protected function saveMessage($content, $headers, $type = 'raw')
    {

        $message = new MessageSent();
        $message->setMessage($content);
        $message->setHeaders($headers);
        $message->setMessageType($type);
        $this->eventDispatcher->dispatch('messageSent', $message);

    }

    /**
     * Close current HTTP connection and wait some secons
     *
     * @param int $sleep The number of seconds to wait for
     */
    protected function closeConnectionAndWait($sleep)
    {
        // cut connexion and wait a few seconds
        ob_end_clean();
        header("Connection: close\r\n");
        header("Content-Encoding: none\r\n");
        ignore_user_abort(true); // optional
        ob_start();
        $size = ob_get_length();
        header("Content-Length: $size");
        ob_end_flush();     // Strange behaviour, will not work
        flush();            // Unless both are called !
        ob_end_clean();
        session_write_close();

        // wait some seconds before sending MDN notification
        sleep($sleep);
    }
}
