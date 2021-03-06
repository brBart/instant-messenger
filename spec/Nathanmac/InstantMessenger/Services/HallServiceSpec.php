<?php

namespace spec\Nathanmac\InstantMessenger\Services;

use Nathanmac\InstantMessenger\Message;
use Nathanmac\InstantMessenger\Services\HallService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GuzzleHttp;

class HallServiceSpec extends ObjectBehavior
{


    function let(GuzzleHttp\Client $client)
    {
        $this->beAnInstanceOf('spec\Nathanmac\InstantMessenger\Services\HallServiceStub');
        $this->beConstructedWith('token');

        $this->setHttpClient($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Nathanmac\InstantMessenger\Services\HallService');
        $this->shouldHaveType('Nathanmac\InstantMessenger\Services\HTTPService');
    }

    function it_sends_a_messages($client)
    {
        // Create a new message.
        $message = new Message();
        $message->from('API');
        $message->body("Simple notification message.");

        $client->post("https://hall.com/api/1/services/generic/token",
            array(
                "json" => array(
                    "title" => 'API',
                    "message" => "Simple notification message."
                )
            )
        )->shouldBeCalled();

        $this->send($message);

        // Send message with an added icon image
        $message->icon('http://img.com');

        $client->post("https://hall.com/api/1/services/generic/token",
            array(
                "json" => array(
                    "title" => 'API',
                    "message" => "Simple notification message.",
                    "picture" => "http://img.com"
                )
            )
        )->shouldBeCalled();

        $this->send($message);
    }

    function it_gets_and_sets_the_token()
    {
        // Get the current key
        $this->getToken()->shouldReturn('token');

        // Set the api key
        $this->setToken('newtoken');

        // Get the current key
        $this->getToken()->shouldReturn('newtoken');
    }

    function it_gets_and_sets_the_icon()
    {
        // Get the current icon url
        $this->getIcon()->shouldReturn(null);

        // Set the icon url
        $this->setIcon('iconurl');

        // Get the current icon url
        $this->getIcon()->shouldReturn('iconurl');
    }
}

class HallServiceStub extends HallService {

    public $client;

    public function setHttpClient($client)
    {
        $this->client = $client;
    }

    public function getHttpClient()
    {
        return $this->client;
    }
}