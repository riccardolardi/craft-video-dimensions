<?php

namespace riccardolardi\craftvideodimensions;

require_once CRAFT_VENDOR_PATH . '/autoload.php';

use Craft;
use craft\base\Plugin;
use craft\elements\Asset;
use craft\events\ModelEvent;
use yii\base\Event;
use getID3;

/**
 * Video Dimensions plugin
 *
 * @method static VideoDimensions getInstance()
 * @author Riccardo Lardi <hello@riccardolardi.com>
 * @copyright Riccardo Lardi
 * @license https://craftcms.github.io/license/ Craft License
 */
class VideoDimensions extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function () {
            $this->attachEventHandlers();
            // ...
        });
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)

        Event::on(
            Asset::class,
            Asset::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                $asset = $event->sender;
                if ($asset->kind == 'video') {
                    // get asset file path
                    $assetFilePath = '';
                    $fsPath = Craft::getAlias($asset->getVolume()->fs->path);
                    $assetFilePath = $fsPath . DIRECTORY_SEPARATOR . $asset->getPath();
                    // get video dimensions
                    $getID3 = new getID3;
                    $file = $getID3->analyze($assetFilePath);
                    $width = $file['video']['resolution_x'];
                    $height = $file['video']['resolution_y'];
                    // get asset record
                    $assetRecord = \craft\records\Asset::findOne($asset->id);
                    // set asset dimensions
                    $assetRecord->width = $width;
                    $assetRecord->height = $height;
                    // save asset record
                    $assetRecord->save(false);
                }
            }
        );
    }
}
