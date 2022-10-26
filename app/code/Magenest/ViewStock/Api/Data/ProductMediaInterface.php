<?php
namespace Magenest\ViewStock\Api\Data;

interface ProductMediaInterface
{
    /**
     * @return string
     */
    public function getMediaType(): string;

    /**
     * @param string $type
     * @return $this
     */
    public function setMediaType(string $type);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url);
}
