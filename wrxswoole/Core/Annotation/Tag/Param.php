<?php
namespace wrxswoole\Core\Annotation\Tag;

use EasySwoole\Annotation\AbstractAnnotationTag;
use EasySwoole\Annotation\ValueParser;
use EasySwoole\Validate\Validate;
use wrxswoole\Core\Annotation\Exception\ParamAnnotationValidateError;
use wrxswoole\Core\Component\CoreCoroutineThread;
use wrxswoole\Core\Validator\Interfaces\ValidateInterface;
use EasySwoole\Validate\Error;

/**
 * Class Param
 *
 * @package EasySwoole\Http\Annotation
 * @Annotation
 */
final class Param extends AbstractAnnotationTag implements ValidateInterface
{

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var array
     */
    public $from = [];

    /**
     *
     * @var string
     */
    public $alias = null;

    /**
     * 以下为校验规则
     */
    public $validateRuleList = [];

    private $allowValidateRule = [
        'activeUrl',
        'alpha',
        'alphaNum',
        'alphaDash',
        'between',
        'bool',
        'decimal',
        'dateBefore',
        'dateAfter',
        'equal',
        'different',
        'equalWithColumn',
        'differentWithColumn',
        'float',
        'func',
        'inArray',
        'integer',
        'isIp',
        'notEmpty',
        'numeric',
        'notInArray',
        'length',
        'lengthMax',
        'lengthMin',
        'betweenLen',
        'money',
        'max',
        'min',
        'regex',
        'allDigital',
        'required',
        'timestamp',
        'timestampBeforeDate',
        'timestampAfterDate',
        'timestampBefore',
        'timestampAfter',
        'url',
        'optional'
    ];

    /**
     *
     * @var string
     */
    public $activeUrl;

    /**
     *
     * @var string
     */
    public $alpha;

    /**
     *
     * @var string
     */
    public $alphaNum;

    /**
     *
     * @var string
     */
    public $alphaDash;

    /**
     *
     * @var string
     */
    public $between;

    /**
     *
     * @var string
     */
    public $bool;

    /**
     *
     * @var string
     */
    public $decimal;

    /**
     *
     * @var string
     */
    public $dateBefore;

    /**
     *
     * @var string
     */
    public $dateAfter;

    /**
     *
     * @var string
     */
    public $equal;

    /**
     *
     * @var string
     */
    public $different;

    /**
     *
     * @var string
     */
    public $equalWithColumn;

    /**
     *
     * @var string
     */
    public $differentWithColumn;

    /**
     *
     * @var string
     */
    public $float;

    /**
     *
     * @var string
     */
    public $func;

    /**
     *
     * @var string
     */
    public $inArray;

    /**
     *
     * @var string
     */
    public $integer;

    /**
     *
     * @var string
     */
    public $isIp;

    /**
     *
     * @var string
     */
    public $notEmpty;

    /**
     *
     * @var string
     */
    public $numeric;

    /**
     *
     * @var string
     */
    public $notInArray;

    /**
     *
     * @var string
     */
    public $length;

    /**
     *
     * @var string
     */
    public $lengthMax;

    /**
     *
     * @var string
     */
    public $lengthMin;

    /**
     *
     * @var string
     */
    public $betweenLen;

    /**
     *
     * @var string
     */
    public $money;

    /**
     *
     * @var string
     */
    public $max;

    /**
     *
     * @var string
     */
    public $min;

    /**
     *
     * @var string
     */
    public $regex;

    /**
     *
     * @var string
     */
    public $allDigital;

    /**
     *
     * @var string
     */
    public $required;

    /**
     *
     * @var string
     */
    public $timestamp;

    /**
     *
     * @var string
     */
    public $timestampBeforeDate;

    /**
     *
     * @var string
     */
    public $timestampAfterDate;

    /**
     *
     * @var string
     */
    public $timestampBefore;

    /**
     *
     * @var string
     */
    public $timestampAfter;

    /**
     *
     * @var string
     */
    public $url;

    /**
     *
     * @var string
     */
    public $optional;

    public function tagName(): string
    {
        return 'Param';
    }

    public function aliasMap(): array
    {
        return [
            static::class
        ];
    }

    public function assetValue(?string $raw)
    {
        $allParams = ValueParser::parser($raw);
        foreach ($allParams as $key => $param) {
            switch ($key) {
                case 'name':
                    {
                        $this->name = (string) $param;
                        break;
                    }
                case 'from':
                    {
                        $this->from = (array) $param;
                        break;
                    }
                case 'alias':
                    {
                        $this->alias = (string) $param;
                    }
                default:
                    {
                        if (in_array($key, $this->allowValidateRule)) {
                            /*
                             * 对inarray 做特殊处理
                             */
                            if (in_array($key, [
                                'inArray',
                                'notInArray'
                            ])) {
                                if (! is_array($param[0])) {
                                    $param = [
                                        $param
                                    ];
                                }
                            }
                            $this->$key = $param;
                            $this->validateRuleList[$key] = true;
                        }
                        break;
                    }
            }
        }
    }

    public function validate()
    {
        $actionArgs = [];
        $validate = new Validate();

        $paramName = $this->name;
        if (empty($paramName)) {
            throw new ParamAnnotationValidateError("param annotation error");
        }
        if (! empty($this->from)) {
            $value = null;

            foreach ($this->from as $from) {
                switch ($from) {
                    case "POST":
                        {
                            $value = CoreCoroutineThread::getInstance()->getCoreController()
                                ->request()
                                ->getParsedBody($paramName);
                            break;
                        }
                    case "GET":
                        {
                            $value = CoreCoroutineThread::getInstance()->getCoreController()
                                ->request()
                                ->getQueryParam($paramName);
                            break;
                        }
                    case "COOKIE":
                        {
                            $value = CoreCoroutineThread::getInstance()->getCoreController()
                                ->request()
                                ->getCookieParams($paramName);
                            break;
                        }
                }
                if ($value !== null) {
                    break;
                }
            }
        } else {
            $value = CoreCoroutineThread::getInstance()->getCoreController()
                ->request()
                ->getRequestParam($paramName);
        }

        CoreCoroutineThread::getInstance()->getCoreController()->addActionArg($paramName, $value);
        if (! empty($this->validateRuleList)) {
            foreach (array_keys($this->validateRuleList) as $rule) {
                $validateArgs = $this->{$rule};
                if (! is_array($validateArgs)) {
                    $validateArgs = [
                        $validateArgs
                    ];
                }
                $validate->addColumn($this->name, $this->alias)->{$rule}(...$validateArgs);
            }
        }

        $data = $actionArgs + CoreCoroutineThread::getInstance()->getCoreController()
            ->request()
            ->getRequestParam();

        if (! $validate->validate($data)) {
            $ex = new ParamAnnotationValidateError([
                'column' => $validate->getError()->getField(),
                'failed_on' => $validate->getError()->getErrorRule()
            ], $validate->getError()->getErrorRuleMsg());
            $ex->setValidate($validate);
            throw $ex;
        }
    }
}
?>