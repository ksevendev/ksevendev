<?php
    
//...


    private function efiConfig() : array
    {
        $sandbox = setting("Efi.sandbox");
        if (!$sandbox) {
            $clientId = setting("Efi.clientId_pro");
            $clientSecret = setting("Efi.clientSecret_pro");
            $certificate = realpath(WRITEPATH . "certs/" . setting("Efi.certificate_pro"));
            //$certificate = WRITEPATH . "" . setting("Efi.certificate_pro");
        } else {
            $clientId = setting("Efi.clientId_dev");
            $clientSecret = setting("Efi.clientSecret_dev");
            $certificate = realpath(WRITEPATH . "certs/" . setting("Efi.certificate_dev"));
            //$certificate = realpath(__DIR__ . "/developmentCertificate.p12");
        }
        $keyPix = setting("Efi.keyPix");
        $debug = setting("Efi.debug");
        $timeExpiration = setting("Efi.timeExpiration");
        $Config = [
            "options" => [
                "client_id"         => $clientId,
                "client_secret"     => $clientSecret,
                "certificate"       => $certificate,
                "sandbox"           => $sandbox,
                "timeout"           => $debug,
            ],
            "keyPix"            => $keyPix,
            "timeExpiration"    => $timeExpiration,
            "urlNotification"   => base_url(),
        ];
        return $Config;
    }

    public function generatePix($invoiceID) : ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            $Result = [
                'data' => null,
                'message' => "Pagina não existe ou você não tem permissão!",
                'status' => false, 
            ];
            return $this->response->setJSON($Result);
        }
        $getInvoice = $this->getInvoice($invoiceID);
        if (!$getInvoice) {
            $Result = [
                'data' => null,
                'message' => "Pagina não existe ou você não tem permissão!",
                'status' => false, 
            ];
            return $this->response->setJSON($Result);
        }

        $Config = $this->efiConfig();

        /*
        $VeryTransaction = $this->Invoice->veryTransaction($getInvoice["ID"]);
        if ($VeryTransaction >= 1) {
            $getTransaction = $this->Invoice->getTransaction($getInvoice["ID"]);
            try {
                $API = EfiPay::getInstance($Config["options"]);
    
            } catch (EfiException $e) {
                //$this->Activitie->Model("User")->Error()->Insert("{Name} Tentou efetuar o pagamento da fatura: {$getInvoice["Code"]}.");
                $Result = [
                    'data' => null,
                    "message" => "Error via EfiException.",
                    "errors" => [
                        "code" => $e->code,
                        "error" => $e->error,
                        "description" => $e->errorDescription,
                    ],
                    'status' => false, 
                ];
                return $this->response->setJSON($Result);
            } catch (\Exception $e) {
                //$this->Activitie->Model("User")->Error()->Insert("{Name} Tentou efetuar o pagamento da fatura: {$getInvoice["Code"]}.");
                $Result = [
                    'data' => null,
                    "message" => "Error via Exception.",
                    "errors" => $e->getMessage(),
                    'status' => false, 
                ];
                return $this->response->setJSON($Result);
            }
        }
        */

        $infoAdicionais = [
            [
                "nome" => "Fatura", 
                "valor" => $getInvoice["Code"], 
            ]
        ];
        foreach ($getInvoice["Items"] AS $Item) {

            $Qntd = $Item["Qntd"] ?? 1;
            $Price = $Item["Price"];
            $TotalProduct = $Qntd * $Price;

            $I["nome"] = $Qntd ."x" . " " . $Item["Name"];
            $I["valor"] = MoneyFormart($TotalProduct);
            $infoAdicionais[] = $I;
        }

        $infoAdicionais = array_merge($infoAdicionais, [
            [
                "nome" => "Subtotal", 
                "valor" => MoneyFormart($getInvoice["Subtotal"]), 
            ],
            [
                "nome" => "Desconto", 
                "valor" => MoneyFormart($getInvoice["Desc"]) . " " . "(" . $getInvoice["DescPorc"] . "%)", 
            ],
            [
                "nome" => "Total", 
                "valor" => MoneyFormart($getInvoice["Total"]), 
            ],
        ]);

        $Body = [
            "calendario" => [
                "expiracao" => (int) $Config["timeExpiration"], 
            ],
            "devedor" => [
                "cpf" => $getInvoice["Client"]["Document"],
                "nome" => $getInvoice["Client"]["Name"] . " " . $getInvoice["Client"]["Last"]
            ],
            "valor" => [
                "original" => "1.00"//number_format($getInvoice["Total"],  2, '.', '')
            ],
            "chave" => $Config["keyPix"], 
            "metadata" => [
                "notification_url" => $Config["urlNotification"],
            ], 
            "infoAdicionais" => $infoAdicionais
        ];
        try {
            $API = EfiPay::getInstance($Config["options"]);
            $pix = $API->pixCreateImmediateCharge($params = [], $Body);
            if ($pix['txid']) {
                $params = [
                    'id' => $pix['loc']['id']
                ];
                $qrcode = $API->pixGenerateQRCode($params);
                //$this->Activitie->Model("User")->Insert("{Name} Efetuou o pagamento da fatura: {$getInvoice["Code"]}.");
                $Data = [
                    "Module" => $this->Module,
                    "Title" => "Pagamento via PIX.",
                    "qrcode" => $qrcode["imagemQrcode"],
                    "code" => $pix["pixCopiaECola"],
                ];
                $Render = $this->Theme('invoices\\modals\\pix', $Data, $this->Module);
                $Result = [
                    'data' => $Render,
                    'message' => "Dados renderizado com sucesso!",
                    "errors" => null,
                    'status' => true, 
                ];
                return $this->response->setJSON($Result);
            } else {
                //$this->Activitie->Model("User")->Insert("{Name} Efetuou o pagamento da fatura: {$getInvoice["Code"]}.");
                $Data = [
                    "Module" => $this->Module,
                    "Title" => "Pagamento via PIX.",
                    "qrcode" => false,
                    "code" => $pix["pixCopiaECola"],
                ];
                $Render = $this->Theme('invoices\\modals\\pix', $Data, $this->Module);
                $Result = [
                    'data' => $Render,
                    'message' => "Dados renderizado com sucesso!",
                    "errors" => null,
                    'status' => true, 
                ];
                return $this->response->setJSON($Result);
            }
        } catch (EfiException $e) {
            //$this->Activitie->Model("User")->Error()->Insert("{Name} Tentou efetuar o pagamento da fatura: {$getInvoice["Code"]}.");
            $Result = [
                'data' => null,
                "message" => "Error via EfiException.",
                "errors" => [
                    "code" => $e->code,
                    "error" => $e->error,
                    "description" => $e->errorDescription,
                ],
                'status' => false, 
            ];
            return $this->response->setJSON($Result);
        } catch (\Exception $e) {
            //$this->Activitie->Model("User")->Error()->Insert("{Name} Tentou efetuar o pagamento da fatura: {$getInvoice["Code"]}.");
            $Result = [
                'data' => null,
                "message" => "Error via Exception.",
                "errors" => $e->getMessage(),
                'status' => false, 
            ];
            return $this->response->setJSON($Result);
        }
    }

//...
