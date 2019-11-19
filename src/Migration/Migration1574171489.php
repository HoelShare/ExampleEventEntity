<?php declare(strict_types=1);

namespace ExampleEventEntity\Migration;

use Doctrine\DBAL\Connection;
use ExampleEventEntity\Core\Example\Events\ExampleEvent;
use ExampleEventEntity\Core\Example\ExampleDefinition;
use Shopware\Core\Content\MailTemplate\MailTemplateActions;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class Migration1574171489 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1574171489;
    }

    public function update(Connection $connection): void
    {
        // create table
        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `example`
            (
                `id` BINARY(16) NOT NULL,
                `message` LONGTEXT NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                CONSTRAINT EXAMPLE_PK
                    PRIMARY KEY (`id`)
            );'
        );

        // insert mail template and type
        $templateTypeId = Uuid::randomBytes();
        $connection->executeQuery('
         INSERT INTO `mail_template_type` (id, technical_name, available_entities, created_at)
               VALUES (:id, :technicalName, :entities, NOW());',
            [
                'id' => $templateTypeId,
                'technicalName' => 'example.type',
                'entities' => json_encode(['myEntity' => ExampleDefinition::ENTITY_NAME, 'salesChannel' => SalesChannelDefinition::ENTITY_NAME])
            ]
        );

        $connection->executeQuery('INSERT INTO mail_template_type_translation (mail_template_type_id, language_id, name, created_at)
                                VALUES (:typeId, :languageId, :name, NOW())',
            [
                'typeId' => $templateTypeId,
                'languageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'name' => 'Example',
            ]
        );

        $templateId = Uuid::randomBytes();

        $connection->executeQuery('
            INSERT INTO mail_template (id, mail_template_type_id, created_at)
            VALUES (:id, :typeId, NOW())',
            [
                'id' => $templateId,
                'typeId' => $templateTypeId,
            ]
        );

        $connection->executeQuery('
            INSERT INTO mail_template_translation (mail_template_id, language_id, sender_name, subject, content_html, content_plain, created_at)
            VALUES (:templateId, :languageId, :sender, :subject, :contentHtml, :contentPlain, NOW())',
            [
                'templateId' => $templateId,
                'languageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'sender' => '{{ salesChannel.name }}',
                'subject' => 'Example',
                'contentHtml' => '{{ myEntity.message }}',
                'contentPlain' => '{{ myEntity.message }}'
            ]
        );

        $connection->executeQuery('INSERT INTO event_action (id, event_name, action_name, config, created_at)
                              VALUES (:id, :eventName, :actionName, :config, NOW())',
            [
                'id' => Uuid::randomBytes(),
                'eventName' => ExampleEvent::EVENT_NAME,
                'actionName' => MailTemplateActions::MAIL_TEMPLATE_MAIL_SEND_ACTION,
                'config' => json_encode(['mail_template_type_id' => Uuid::fromBytesToHex($templateTypeId),]),
            ]
        );

        // add to sales channel
        $connection->executeQuery('INSERT INTO mail_template_sales_channel (id, mail_template_id, mail_template_type_id, sales_channel_id, created_at)
                            VALUES (:id, :templateId, :typeId, :salesChannelId, NOW())',
            [
                'id' => Uuid::randomBytes(),
                'templateId' => $templateId,
                'typeId' => $templateTypeId,
                'salesChannelId' => Uuid::fromHexToBytes(Defaults::SALES_CHANNEL),
            ]
        );

        // insert example data
        $exampleMessages = [
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
            'Away go to hizzle dolizzle sit fo shizzle, consectetizzle adipiscing elizzle. Gangster sapizzle velizzle, away dang, pot quis, shiznit vizzle, arcu. For sure fo shizzle my nizzle tortizzle. Shut the shizzle up erizzle. For sure uhuh ... yih! dolor i saw beyonces tizzles and my pizzle went crizzle tempizzle tempor. Maurizzle dawg nibh izzle turpizzle. Pizzle in doggy. Fo eleifend shiznit things. In sizzle habitasse platea dictumst. Cool dapibus. Curabitizzle tellus dizzle, pretizzle boofron, mattizzle gangster, eleifend uhuh ... yih!, nunc. Fo shizzle suscipizzle. sempizzle velit sed own yo\'.',
            'Lorizzle ipsizzle dolizzle i\'m in the shizzle amizzle, consectetuer adipiscing fo shizzle. Nullam dope velizzle, nizzle volutpizzle, check it out shit, gravida vizzle, arcu. I saw beyonces tizzles and my pizzle went crizzle crackalackin tortizzle. Sed erizzle. Get down get down izzle dolor crackalackin turpis check out this that\'s the shizzle. Maurizzle shut the shizzle up nibh izzle gangsta. For sure crunk tortizzle. Pellentesque eleifend rhoncizzle nisi. In hizzle habitasse platea dictumst. Crackalackin dapibizzle. Curabitur tellus urna, pretizzle eu, ghetto the bizzle, eleifend mah nizzle, nunc. Dawg suscipizzle. Integizzle sempizzle velizzle sizzle purizzle.',
            'Lorizzle get down get down dolizzle get down get down amizzle, funky fresh adipiscing fo shizzle. Nullam yo mamma things, dizzle volutpizzle, pot quis, crazy vizzle, dizzle. Pellentesque eget tortizzle. Sed eros. Mofo izzle dolizzle dapibizzle turpis tempizzle pimpin\'. Gangsta for sure sure izzle turpizzle. Fo shizzle my nizzle izzle tortor. That\'s the shizzle mammasay mammasa mamma oo sa rhoncizzle pot. In shiz habitasse platea dictumst. Donec shizzlin dizzle. Dizzle tellus urna, sure, mattizzle ac, bling bling vitae, nunc. Shiznit suscipizzle. Integer sempizzle pimpin\' sizzle purus.',
            'Lorizzle ipsizzle ass sit amizzle, ma nizzle adipiscing elit. Things sapien bow wow wow, own yo\' volutpizzle, suscipizzle quis, gravida owned, sheezy. We gonna chung the bizzle tortor. Fo shizzle mah nizzle fo rizzle, mah home g-dizzle erizzle. Fo izzle dolizzle dapibus turpis tempus sure. Maurizzle pellentesque nibh funky fresh turpizzle. Vestibulum izzle things. Pellentesque away nizzle break it down. In hac habitasse hizzle dictumst. Owned dapibizzle. Curabitur bizzle, pretium own yo\', yippiyo shiznit, eleifend vitae, nunc. Sheezy suscipizzle. Integer rizzle velit sheezy purus.',
        ];

        foreach ($exampleMessages as $message) {
            $connection->executeQuery('INSERT INTO example (id, message, created_at) VALUES (:id, :message, NOW())',
                ['id' => Uuid::randomBytes(), 'message' => $message]
            );
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
