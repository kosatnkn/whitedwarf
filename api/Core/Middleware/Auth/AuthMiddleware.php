<?php

namespace Api\Core\Middleware\Auth;

use Api\Core\Exceptions\Types\MiddlewareException;
use Api\Core\Adapters\Auth\JWTAdapterException;

class AuthMiddleware
{
    /**
     * Authentication middleware invokable class.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     * @return \Psr\Http\Message\ResponseInterface
     * @throws MiddlewareException
     */
    public function __invoke($request, $response, $next)
    {
        // inbound manipulations

        $strToken = '';

        // by default check whether the token is sent in the request header
        if($request->hasHeader('Authorization'))
        {
            $arrToken = explode(' ', $request->getHeader('Authorization')[0]);
            
            if($arrToken[0] != 'Bearer')
            {
                JWTAdapterException::noBearerToken();
            }

            if(!isset($arrToken[1]) || $arrToken[1] == '')
            {
                JWTAdapterException::noToken();
            }

            $strToken = $arrToken[1];
        }
        else
        {
            // otherwise check to see whether the token is sent in the request query
             $arrQueryParams = $request->getQueryParams();
             $strToken = isset($arrQueryParams['token']) ? $arrQueryParams['token'] : '';
        }

        $authenticator = $GLOBALS['auth'];
        $authenticator->authenticate($strToken);

        // pass to next level
        $response = $next($request, $response);

        // outbound manipulations
        
        return $response;
    }
}