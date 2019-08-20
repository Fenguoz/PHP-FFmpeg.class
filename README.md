# PHP-FFmpeg.class
根据FFmpeg整理一个简单类文件，实现音频合成、图片背景音乐合成

```php
<?php
//示例
$AudioMix = new AudioMix();
$AudioMix->setDuration(12);
$AudioMix->setPath('video','a.mp4');
$AudioMix->setPath('music','a.mp3');
$AudioMix->setOutput('output.mp4');
$AudioMix->build('v2m');
?>
```
