<?php

namespace PhpMcp\Http\Tool;

class DingTalkNotifyTool implements ToolInterface
{
    // 钉钉机器人 Webhook 地址模板，access_token 通过环境变量 DINGTALK_ACCESS_TOKEN 传入
    private const WEBHOOK_TEMPLATE = 'https://oapi.dingtalk.com/robot/send?access_token=%s';

    public function name(): string
    {
        return 'dingtalk_notify';
    }

    public function definition(): array
    {
        return [
            'name' => $this->name(),
            'description' => '发送钉钉机器人文本通知，access_token 通过环境变量 DINGTALK_ACCESS_TOKEN 配置',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'content' => [
                        'type' => 'string',
                        'description' => '要发送的通知文本内容',
                    ],
                ],
                'required' => ['content'],
            ],
        ];
    }

    public function execute(array $args): string
    {
        $content = $args['content'] ?? '';
        if ($content === '') {
            return '错误：缺少通知内容 content 参数';
        }

        $accessToken = getenv('DINGTALK_ACCESS_TOKEN');
        if ($accessToken === false || $accessToken === '') {
            return '错误：未配置环境变量 DINGTALK_ACCESS_TOKEN';
        }

        $webhook = sprintf(self::WEBHOOK_TEMPLATE, $accessToken);

        $payload = json_encode([
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ],
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return '错误：消息内容 JSON 编码失败';
        }

        $ch = curl_init($webhook);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return '错误：发送失败 - ' . $error;
        }

        return sprintf("钉钉通知已发送（HTTP %d）：%s", $httpCode, $response);
    }
}
