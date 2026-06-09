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
                        <td align="center" style="background:#1e3a8a;padding:30px;">

                            <img
                                src="https://s3-database.mundoevogard.com/logos/evogard.png"
                                alt="Evogard"
                                style="max-width:220px;height:auto;display:block;margin-bottom:20px;"
                            >

                            <h1 style="margin:0;color:#ffffff;font-size:24px;">
                                Pagamento Realizado
                            </h1>

                            <p style="margin-top:10px;color:#dbeafe;font-size:14px;">
                                Confirmação de pagamento ao prestador
                            </p>

                        </td>
                    </tr>

                    <!-- Conteúdo -->
                    <tr>
                        <td style="padding:40px;">

                            <p style="font-size:16px;color:#333333;line-height:1.8;margin-top:0;">
                                Olá,
                                <strong>{{ $payload['Nome do Responsável'] ?? 'Prestador' }}</strong>.
                            </p>

                            <p style="font-size:16px;color:#333333;line-height:1.8;">
                                Informamos que o pagamento referente aos serviços prestados foi
                                processado e realizado com sucesso.
                            </p>

                            <!-- Card de informações -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="margin:30px 0;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;">
                                <tr>
                                    <td style="padding:25px;">

                                        <h2 style="margin-top:0;color:#1e3a8a;font-size:18px;">
                                            Dados do Prestador
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

                                    </td>
                                </tr>
                            </table>

                            <!-- Confirmação -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="background:#ecfdf5;border-left:5px solid #10b981;border-radius:8px;">
                                <tr>
                                    <td style="padding:20px;color:#065f46;font-size:15px;line-height:1.6;">
                                        O pagamento foi concluído e encaminhado para os dados
                                        bancários cadastrados em nosso sistema.
                                    </td>
                                </tr>
                            </table>

                            <p style="margin-top:30px;font-size:16px;color:#333333;line-height:1.8;">
                                Agradecemos pela parceria e pela confiança em nossa rede de prestadores.
                            </p>

                            <p style="font-size:16px;color:#333333;line-height:1.8;">
                                Atenciosamente,<br>
                                <strong>Equipe Financeira</strong><br>
                                Guardian Proteção Veicular
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