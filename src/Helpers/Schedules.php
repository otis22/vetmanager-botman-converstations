<?php

namespace Otis22\VetmanagerConversations\Helpers;

use GuzzleHttp\Client;
use Otis22\VetmanagerToken\Token;
use Otis22\VetmanagerRestApi\Query\Filter\MoreThan;
use Otis22\VetmanagerRestApi\Query\Filter\LessThan;
use Otis22\VetmanagerRestApi\Model\Property;
use Otis22\VetmanagerRestApi\Query\Filter\Value\StringValue;
use Otis22\VetmanagerRestApi\Query\Filter\EqualTo;
use Otis22\VetmanagerRestApi\Query\Filters;

use function Otis22\VetmanagerRestApi\uri;

class Schedules
{
    /**
     * @var Token
     */
    private $token;
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * Schedules constructor.
     * @param Token $token
     * @param Client $httpClient
     */
    public function __construct(Token $token, Client $httpClient)
    {
        $this->token = $token;
        $this->httpClient = $httpClient;
    }

    /**
     * @param int $days
     * @param int $doctor_id
     * @param int $clinic_id
     * @return array{success:bool}
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function byIntervalInDays($days = 7, $doctor_id = 0, $clinic_id = 0): array
    {
        $now = date("Y-m-d");
        $filteringParams[] = new MoreThan(
            new Property('begin_datetime'),
            new StringValue($now . " 00:00:00")
        );
        $filteringParams[] = new LessThan(
            new Property('end_datetime'),
            new StringValue(date('Y-m-d', intval(strtotime($now . " +" . $days . " days"))) . " 23:59:59")
        );
        if ($doctor_id) {
            $filteringParams[] = new EqualTo(
                new Property('doctor_id'),
                new StringValue(strval($doctor_id))
            );
        }
        if ($clinic_id) {
            $filteringParams[] = new EqualTo(
                new Property('clinic_id'),
                new StringValue(strval($clinic_id))
            );
        }
        $filters = new Filters(...$filteringParams);
        $request = $this->httpClient->request(
            'GET',
            uri("timesheet")->asString(),
            [
                "query" => $filters->asKeyValue()
            ]
        );
        $result = json_decode(
            strval(
                $request->getBody()
            ),
            true
        );
        return $result;
    }
}
