<?php
namespace X\Constant;

/**
 * HTTP status code constants.
 *
 * Standard HTTP response status codes as defined in RFC 7231.
 *
 * Usage:
 * ```php
 * use const X\Constant\HTTP_OK;
 * use const X\Constant\HTTP_NOT_FOUND;
 *
 * http_response_code(HTTP_OK);
 * ```
 */

// 1xx Informational
/** @var int 100 Continue */
const HTTP_CONTINUE = 100;
/** @var int 101 Switching Protocols */
const HTTP_SWITCHING_PROTOCOLS = 101;

// 2xx Success
/** @var int 200 OK */
const HTTP_OK = 200;
/** @var int 201 Created */
const HTTP_CREATED = 201;
/** @var int 202 Accepted */
const HTTP_ACCEPTED = 202;
/** @var int 203 Non-Authoritative Information */
const HTTP_NONAUTHORITATIVE_INFORMATION = 203;
/** @var int 204 No Content */
const HTTP_NO_CONTENT = 204;
/** @var int 205 Reset Content */
const HTTP_RESET_CONTENT = 205;
/** @var int 206 Partial Content */
const HTTP_PARTIAL_CONTENT = 206;

// 3xx Redirection
/** @var int 300 Multiple Choices */
const HTTP_MULTIPLE_CHOICES = 300;
/** @var int 301 Moved Permanently */
const HTTP_MOVED_PERMANENTLY = 301;
/** @var int 302 Found */
const HTTP_FOUND = 302;
/** @var int 303 See Other */
const HTTP_SEE_OTHER = 303;
/** @var int 304 Not Modified */
const HTTP_NOT_MODIFIED = 304;
/** @var int 305 Use Proxy */
const HTTP_USE_PROXY = 305;
/** @var int 306 (Unused) */
const HTTP_UNUSED = 306;
/** @var int 307 Temporary Redirect */
const HTTP_TEMPORARY_REDIRECT = 307;

// 4xx Client Error
/** @var int 400 Bad Request */
const HTTP_BAD_REQUEST = 400;
/** @var int 401 Unauthorized */
const HTTP_UNAUTHORIZED = 401;
/** @var int 402 Payment Required */
const HTTP_PAYMENT_REQUIRED = 402;
/** @var int 403 Forbidden */
const HTTP_FORBIDDEN = 403;
/** @var int 404 Not Found */
const HTTP_NOT_FOUND = 404;
/** @var int 405 Method Not Allowed */
const HTTP_METHOD_NOT_ALLOWED = 405;
/** @var int 406 Not Acceptable */
const HTTP_NOT_ACCEPTABLE = 406;
/** @var int 407 Proxy Authentication Required */
const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
/** @var int 408 Request Timeout */
const HTTP_REQUEST_TIMEOUT = 408;
/** @var int 409 Conflict */
const HTTP_CONFLICT = 409;
/** @var int 410 Gone */
const HTTP_GONE = 410;
/** @var int 411 Length Required */
const HTTP_LENGTH_REQUIRED = 411;
/** @var int 412 Precondition Failed */
const HTTP_PRECONDITION_FAILED = 412;
/** @var int 413 Request Entity Too Large */
const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
/** @var int 414 Request-URI Too Long */
const HTTP_REQUEST_URI_TOO_LONG = 414;
/** @var int 415 Unsupported Media Type */
const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
/** @var int 416 Requested Range Not Satisfiable */
const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
/** @var int 417 Expectation Failed */
const HTTP_EXPECTATION_FAILED = 417;

// 5xx Server Error
/** @var int 500 Internal Server Error */
const HTTP_INTERNAL_SERVER_ERROR = 500;
/** @var int 501 Not Implemented */
const HTTP_NOT_IMPLEMENTED = 501;
/** @var int 502 Bad Gateway */
const HTTP_BAD_GATEWAY = 502;
/** @var int 503 Service Unavailable */
const HTTP_SERVICE_UNAVAILABLE = 503;
/** @var int 504 Gateway Timeout */
const HTTP_GATEWAY_TIMEOUT = 504;
/** @var int 505 HTTP Version Not Supported */
const HTTP_VERSION_NOT_SUPPORTED = 505;
