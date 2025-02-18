<?php // what's sent from the server back to the caller if in the original request

namespace Survos\SaisBundle\Model;

use App\Entity\Thumb;

class ThumbPayload
{
    public function __construct(
        public string $mediaCode,
        public string $liipCode,
        public string $url,
        public int $size
    ) {
    }

}
