<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2017-05-08
 * Time: 14:35
 */

namespace Oasis\Mlib\AwsWrappers;

use Aws\Credentials\CredentialProvider;
use Aws\DoctrineCacheAdapter;
use Doctrine\Common\Cache\FilesystemCache;
use Oasis\Mlib\Utils\Exceptions\MandatoryValueMissingException;

class AwsConfigDataProvider
{
    protected $config;
    
    /**
     * AwsConfigDataProvider constructor.
     *
     * @param array $data
     * @param null  $version
     */
    public function __construct(array $data, $version = null)
    {
        if (!isset($data['region'])) {
            throw new MandatoryValueMissingException("Region must be specified in the AWS config!");
        }
        if (!isset($data['config']) && $version) {
            $data['version'] = $version;
        }
        if (!isset($data['credentials'])){
            throw new MandatoryValueMissingException('Data \'Credentials\' is not set.'."\r".print_r($data, true));
        }
        if (!isset($data['profile']))
        {
            throw new MandatoryValueMissingException('Data \'Profile\' is not set.'."\r".print_r($data, true));
        }
        if (!isset($data['iamrole']))
        {
            throw new MandatoryValueMissingException('Data \'I am Role\' is not set.'."\r".print_r($data, true));
        }
        if (!getenv('AWS_ACCESS_KEY_ID') || !getenv('AWS_SECRET_ACCESS_KEY')) {
            if (!getenv('AWS_SECRET_ACCESS_KEY')) {
                throw new MandatoryValueMissingException('Environment \'AWS Access Key\' is not set.' . "\r" . print_r(getenv()));
            }
            if (!getenv('AWS_ACCESS_KEY_ID')) {
                throw new MandatoryValueMissingException('Environment \'AWS Access Key ID\' is not set.' . "\r" . print_r(getenv()));
            }
        }
        if (!isset($data['credentials'])
            && !isset($data['profile'])
            && (!getenv('AWS_ACCESS_KEY_ID') || !getenv('AWS_SECRET_ACCESS_KEY'))
            && !getenv('AWS_SESSION_TOKEN')
            && !(isset($data['iamrole']) && $data['iamrole'] != false)
        ) {
            throw new MandatoryValueMissingException("Credentials information not provided in the AWS config!");
        }
        if (isset($data['credentials']) && $data['credentials'] instanceof TemporaryCredential) {
            /** @var TemporaryCredential $tc */
            $tc                  = $data['credentials'];
            $data['credentials'] = [
                'key'    => $tc->accessKeyId,
                'secret' => $tc->secretAccessKey,
                'token'  => $tc->sessionToken,
            ];
        }
        elseif (isset($data['iamrole']) && $data['iamrole']) {
            $cacheFile = $data['iamrole'];
            if ($cacheFile === true) {
                $cacheFile = \sys_get_temp_dir() . "/iam.role.cache";
            }
            $cacheAdapter        = new DoctrineCacheAdapter(new FilesystemCache($cacheFile));
            $data['credentials'] = CredentialProvider::cache(CredentialProvider::ecsCredentials(), $cacheAdapter);
        }
        
        $this->config = $data;
        
    }
    
    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
    
}
