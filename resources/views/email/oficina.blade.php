<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Realizado</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9;padding:30px 0;">
        <tr>
            <td align="center">

                <table width="650" cellpadding="0" cellspacing="0"
                       style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.08);">

                    <!-- Cabeçalho -->
                    <tr>
                        <td align="center" style="background:#0f2a44;padding:30px;">

                            <img
                                src="https://s3-database.mundoevogard.com/logos/evogard.png"
                                alt="Evogard"
                                style="max-width:220px;height:auto;display:block;margin:0 auto 20px;"
                            >

                            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td align="center" style="padding:0 10px;">
                                        <img
                                            src="https://s3-database.mundoevogard.com/logos/cobertura-total.png"
                                            alt="Cobertura Total"
                                            style="max-width:140px;height:auto;display:block;"
                                        >
                                    </td>
                                    <td align="center" style="padding:0 10px;">
                                        <img
                                            src="https://s3-database.mundoevogard.com/logos/meu-veiculo.png"
                                            alt="Meu Veículo"
                                            style="max-width:140px;height:auto;display:block;"
                                        >
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Título -->
                    <tr>
                        <td align="center" style="background:#ffffff;padding:25px 30px 0;">

                            <h1 style="margin:0;color:#0f2a44;font-size:24px;">
                                Pagamento Realizado
                            </h1>

                            <p style="margin-top:10px;color:#16a974;font-size:14px;font-weight:bold;">
                                Confirmação de pagamento do(a) {{ $payload['Tipo de vínculo'] ?? 'Oficina' }}
                            </p>

                        </td>
                    </tr>

                    <!-- Conteúdo -->
                    <tr>
                        <td style="padding:40px;">

                            <p style="font-size:16px;color:#333333;line-height:1.8;margin-top:0;">
                                Olá,
                                <strong>{{ $payload['Nome do Responsável'] ?? '--' }}</strong>.
                            </p>

                            <p style="font-size:16px;color:#333333;line-height:1.8;">
                                Informamos que o pagamento referente aos serviços prestados foi
                                processado e realizado com sucesso.
                            </p>

                            <!-- Card de informações -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="margin:30px 0;background:#f8fafc;border:1px solid #e5e7eb;border-left:4px solid #16a974;border-radius:10px;">
                                <tr>
                                    <td style="padding:25px;">

                                        <h2 style="margin-top:0;color:#0f2a44;font-size:18px;">
                                            Dados do Beneficiário
                                        </h2>

                                        <p style="margin:12px 0;color:#333333;">
                                            <strong>Oficina:</strong><br>
                                            {{ $payload['Nome da Oficina'] ?? '-' }}
                                        </p>

                                        <p style="margin:12px 0;color:#333333;">
                                            <strong>CNPJ:</strong><br>
                                            {{ $payload['CNPJ'] ?? '-' }}
                                        </p>

                                        <p style="margin:12px 0;color:#333333;">
                                            <strong>Responsável:</strong><br>
                                            {{ $payload['Nome do Responsável'] ?? '-' }}
                                        </p>

                                        <p style="margin:12px 0;color:#333333;">
                                            <strong>Endereço:</strong><br>
                                            {{ $payload['Endereço Completo'] ?? '-' }}
                                        </p>

                                        <p style="margin:12px 0;color:#333333;">
                                            <strong>Valor:</strong><br>
                                            R$ {{ isset($payload['valor']) ? number_format($payload['valor'], 2, ',', '.') : '0,00' }}
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            <!-- Confirmação -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="background:#ecfdf5;border-left:5px solid #10b981;border-radius:8px;">
                                <tr>
                                    <td width="40" valign="top" style="padding:20px 0 20px 20px;font-size:22px;line-height:1;">
                                        💰
                                    </td>
                                    <td style="padding:20px 20px 20px 0;color:#065f46;font-size:15px;line-height:1.6;">
                                        O pagamento foi dado baixa no dia
                                            <strong>{{ isset($payload['payment_date'])
                                                ? \Carbon\Carbon::parse($payload['payment_date'])->format('d/m/Y')
                                                : 'não informado'
                                            }}</strong>.
                                    </td>
                                </tr>
                            </table>

                            <!-- Dados bancários da baixa -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="margin-top:20px;background:#eef3f7;border-left:5px solid #0f2a44;border-radius:8px;">
                                <tr>
                                    <td width="40" valign="top" style="padding:20px 0 20px 20px;font-size:22px;line-height:1;">
                                        🏦
                                    </td>
                                    <td style="padding:20px 20px 20px 0;color:#0f2a44;font-size:15px;line-height:1.6;">
                                        <strong>Dados bancários da baixa:</strong>
                                        <br><br>

                                        <strong>Titular:</strong> {{ $payload['Titular'] ?? '-' }}<br>
                                        <strong>Banco:</strong> {{ $payload['Banco'] ?? '-' }}<br>
                                        <strong>Agência:</strong> {{ $payload['Agência'] ?? '-' }}<br>
                                        <strong>Conta e Dígito:</strong> {{ $payload['Conta e Digito'] ?? '-' }}<br>

                                        @if(($payload['Tipo de Chave Pix'] ?? null) === 'E-mail' && !empty($payload['PIX - E-mail']))
                                            <strong>PIX (E-mail):</strong> {{ $payload['PIX - E-mail'] }}<br>
                                        @elseif(!empty($payload['Tipo de Chave Pix']) && !empty($payload['PIX - Chave Aleatória']))
                                            <strong>PIX ({{ $payload['Tipo de Chave Pix'] }}):</strong> {{ $payload['PIX - Chave Aleatória'] }}<br>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <p style="margin-top:30px;font-size:16px;color:#333333;line-height:1.8;">
                                Agradecemos pela parceria e pela confiança em nossa rede de prestadores.
                            </p>

                            <p style="font-size:16px;color:#333333;line-height:1.8;">
                                Atenciosamente,<br>
                                <strong>Equipe Financeira</strong><br>
                            </p>

                        </td>
                    </tr>

                    <!-- Rodapé -->
                    <tr>
                        <td align="center"
                            style="background:#f8fafc;padding:20px;color:#6b7280;font-size:12px;line-height:1.6;">
                            Este é um e-mail automático enviado pelo sistema da Guardian Proteção Veicular.<br>
                            Por favor, não responda esta mensagem.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>