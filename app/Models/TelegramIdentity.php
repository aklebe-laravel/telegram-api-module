<?php

namespace Modules\TelegramApi\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperTelegramIdentity
 */
class TelegramIdentity extends Model
{
    use HasFactory;
    use TraitBaseModel;

    const TYPE_GROUP = 'group';
    const TYPE_CHANNEL = 'channel';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'telegram_identities';

    /**
     * values should cast from json to an array and via versa
     *
     * @var string[]
     */
    protected $casts = [
        // 'detector_list'   => 'array',
        'additional_data' => 'array',
    ];

    /**
     * @return BelongsToMany
     */
    public function finders(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'telegram_identity_findings', 'entity_id', 'finder_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'telegram_identity_findings', 'finder_id', 'entity_id')
            ->withTimestamps();
    }


}
