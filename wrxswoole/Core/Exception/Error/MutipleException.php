<?php
namespace wrxswoole\Core\Exception\Error;

use App\App;
use wrxswoole\Core\Exception\Component\ExceptionHandler;

/**
 *
 * @author WANG RUNXIN
 *        
 */
class MutipleException extends BaseException
{

    public $hint = null;

    public $classes = [];

    function __construct(array $errors)
    {
        $this->message = [];

        foreach ($errors as $error) {

            if ($error instanceof Notice) {
                $this->classes[Notice::class] = true;
            } else {
                $this->classes[get_class($error)] = true;
            }

            $this->hint[] = ExceptionHandler::formatExceptionTrace($error);

            if ($error instanceof BaseException) {
                $this->message[] = empty($error->getMessage()) ? App::DEFAULT_ERROR_MSG : $error->getMessage();
            } else {
                $this->message[] = $error->getMessage();
            }
        }

        $this->message = implode(", ", $this->message);
    }

    public function isNotice(): bool
    {
        if (count($this->classes) != 1) {
            return false;
        }

        if (isset($this->classes[Notice::class])) {
            return true;
        }

        return false;
    }
}

?>