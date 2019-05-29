<?php declare(strict_types=1);

namespace Src\IspService;

use Src\IspService\IspException;

/**
 * Class IspConnector
 * @package Src\IspService
 */
class IspConnector
{
    /** @var string $strUrlWsdl */
    private $strUrlWsdl = '';
    /** @var string $strLogin */
    private $strLogin = '';
    /** @var string $strPassword */
    private $strPassword = '';

    public function __construct(string $strLogin, string $strPass, string $strUrl)
    {
        $this->strLogin = $strLogin;
        $this->strPassword = $strPass;
        $this->strUrlWsdl = $strUrl;

    }

    /**
     * request
     *
     * @param bool $strMethod
     * @param array $arParams
     *
     * @return array
     *
     * @throws \SoapFault
     */
    public function request($strMethod = false, array $arParams = []): array
    {
        try {
            /** @var \SoapClient $obClient */
            $obClient = new \SoapClient(
                $this->strUrlWsdl,
                [
                    'exceptions' => 1,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'encoding' => 'UTF-8'
                ]
            );
        } catch (SoapFault $e) {
            throw new \Exception($e->getMessage());
        }

        $arParams['LoginIsp'] = $this->strLogin;
        $arParams['PasswIsp'] = $this->strPassword;

        try {
            /** @var \SoapClient $arResponse */
            $arResponse = (array)$obClient->$strMethod($arParams);
        } catch (SoapFault $e) {
            throw new IspException($e->getMessage(), $e->getCode());
        }

        if ($arResponse['ResCode'] !== 0) {
            if ($arResponse['ResCode'] === 221) {
                /** @var array $arParams */
                $arParams = [
                    'LoginIsp' => $this->strLogin,
                    'PasswIsp' => $this->strPassword,
                    'Identity' => $arParams['Identity']
                ];

                try {
                    /** @var \SoapClient $arResponse */
                    $arResponse = (array)$obClient->GetStatusByIdent($arParams);
                    return $arResponse;
                } catch (SoapFault $e) {
                    throw new \Exception($e->getMessage(), $e->getCode());
                }
            }

            throw new \Exception($arResponse['ResMsg'], $arResponse['ResCode']);
        }

        return $arResponse;
    }
}

