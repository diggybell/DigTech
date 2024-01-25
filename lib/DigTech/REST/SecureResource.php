<?php

namespace DigTech\REST;

use DigTech\Logging\Logger as Logger;

/**
 * \class SecureResource
 * \ingroup API
 * \brief This class adds authentication/access control functionality
 */
abstract class SecureResource extends Resource
{
    protected $_authorizationHeader;
    protected $_authUser = null; ///< AuthUser object

    /**
     * \brief Construct the object, allow for CORS, assemble and pre-process the data
     * \param $request The request that was received
     * \param $schema The default schema to be used for this API
     */
    public function __construct($request, $schema = 'digtech')
    {
        
        $headers = apache_request_headers();
        if (array_key_exists('Authorization', $headers))
        {
            $this->_authorizationHeader = $headers['Authorization'];
        }
        
        if ($this->isAuthorized())
        {
            parent::__construct($request, $schema);
        }
    }

    /**
     * \brief Check request credentials
     * \returns true/false to indicate if request is authorized
     */
    protected function isAuthorized()
    {
        $ret = false;

        /**
         * \todo This is where the authentication logic should be placed. The auth handler can
         * interpret the Authorization header as desired.
         */
        // $ret = ($this->_authUser->userAuth() && $this->_authUser->appAuth());

        $ret = true; ///< \todo This needs to be fixed

        return $ret;
    }

    /**
     * \brief Dispatch the request if it is authorized. Otherwise return HTTP 401
     */
    public function dispatch()
    {
        // Make sure request is authorized
        if (!$this->isAuthorized())
        {
            $output = $this->response("Not authorized to access this API", 401);
            Logger::log("Secure: Authorization failure\n");
        }
        else
        {
            $output = parent::dispatch();
        }
        return $output;
    }
}
