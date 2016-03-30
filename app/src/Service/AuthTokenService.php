<?php
/**
 * CAS Auth Token Service
 *
 * @category usf-it
 * @package slim-skeleton
 * @author Eric Pierce <epierce@usf.edu>
 * @license http://www.opensource.org/licenses/MIT MIT
 * @link https://github.com/USF-IT/slim-skeleton
 */
namespace USF\IdM\AuthTransfer\AuthToken\Service;

use Psr\Log\LoggerInterface;
use Slim\Collection;
use \Firebase\JWT\JWT;

/**
 *
 *
 * @category usf-it
 * @package slim-skeleton
 * @author Eric Pierce <epierce@usf.edu>
 * @license http://www.opensource.org/licenses/MIT MIT
 * @link https://github.com/USF-IT/slim-skeleton
 */
class AuthTokenService implements \USF\IdM\AuthTransfer\AuthTransferServiceInterface {
    private $logger;
    private $settings;

    public function __construct(LoggerInterface $logger, Collection $settings )
    {
        $this->logger = $logger;
        $this->settings = $settings;

        // Pull config data from the settings object
        //$serviceConfig = $settings['example_config'];

        // If you need to configure your service object, do it here and  add it as a
        // private property to the class.
    }

    public function getRedirectUrl($paramMap) {
        return \trim($paramMap['casURL'],'/')."/login?".
        \http_build_query(\array_filter($paramMap, function($key) {
            return !\in_array($key, ['casURL','json_data']);
        }, \ARRAY_FILTER_USE_KEY));
    }
    
    public function getToken($attributeMap, $keyValue) {
        return \call_user_func(function($token) use ($keyValue) {
            return [
                "json" => \json_encode($token),
                "final" => JWT::encode($token, $keyValue)
            ];
        }, [
            "generated" => (new \DateTime())->getTimestamp(),
            "credentials" => $attributeMap
        ]);
    }
    
}
