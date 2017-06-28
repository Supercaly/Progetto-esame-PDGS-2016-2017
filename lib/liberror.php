<?php
/*
 *
*/
class Errore extends ErrorException{
    private $error_info;
    function __construct(string $info)
    {
      $this->error_info = $info;
    }

    public function getErrInfo(){
      return $this->error_info;
    }
}
/*
 * The server could not understand your request.
 * Verify that request parameters (and content, if any)
 * are valid.
*/
class BadRequest extends Errore
{
  function __construct(string $s = null)
  {
    if($s == null)
      $s = 'BadRequest';
    parent::__construct($s);
  }
}
/*
 * Authentication failed or was not provided.
 * Verify that you have sent valid credentials
 * via an api_key parameter.
*/
class AuthorizationRequired  extends Errore
{
  function __construct(string $s = null)
  {
    if($s == null)
      $s = 'Authorization Required';
    parent::__construct($s);
  }
}
/*
 * The server understood your request and verified
 * your credentials, but you are not allowed to perform
 * the requested action.
*/
class Forbidden extends Errore
{
  function __construct(string $s = null)
  {
    if($s == null)
      $s = 'Forbidden';
    parent::__construct($s);
  }
}
/*
 * The resource that you requested does not exist.
 * Verify that any object id provided is valid.
*/
class NotFound extends Errore
{
  function __construct(string $s = null)
  {
    if($s == null)
      $s = 'Not Found';
    parent::__construct($s);
  }
}
/*
 * The resource identified by the request is only
 * capable of generating a desired response.
 * In fact it means the requested content type
 * is not available. Try JSON or XML.
*/
class NotAcceptable extends Errore
{
  function __construct(string $s = null)
  {
    if($s == null)
      $s = 'Not Acceptable';
    parent::__construct($s);
  }
}
/*
 * An unknown error has occurred.
*/
class InternalServerError extends Errore
{
  function __construct(string $s = null)
  {
    if($s == null)
      $s = 'Internal Server Error';
    parent::__construct($s);
  }
}
/*
 * The wheelmap service is currently not available.
*/
class TemporarilyNotAvailable extends Errore
{
  function __construct(string $s = null)
  {
    if($s == null)
      $s = 'Temporarily not available';
    parent::__construct($s);
  }
}
/*
 * Un errore diverso da quelli sopra citati
*/
class ErrorNotListed extends Errore
{
  function __construct(string $s = null)
  {
    if($s == null)
      $s = 'Error not listed';
    parent::__construct($s);
  }
}
?>
