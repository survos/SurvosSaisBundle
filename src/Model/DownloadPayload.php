<?php // what's sent from the server back to the caller if in the original request

namespace Survos\SaisBundle\Model;

use App\Entity\Thumb;

class DownloadPayload
{
    public function __construct(
        public string $mediaCode,
        public array $thumbData
    ) {
    }

}
