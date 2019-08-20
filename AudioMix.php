<?php
namespace App\Libraries;

class AudioMix
{
    protected $output;//输出文件
    protected $duration = 12;//播放持续时间
    protected $videoPath;//视频路径
    protected $imagePath;//图片路径
    protected $musicPath;//音乐路径
    protected $start;
    protected $end;
    protected $filter_complex;

    /**
     * 设置文件及路径
     * @param string $type 类型
     * @param string $path
     */
    public function setPath(string $type, string $path)
    {
        switch ($type) {
            case 'video'://视频
                $this->videoPath = $path;
                break;
            case 'image'://图片
                $this->imagePath = $path;
                break;
            case 'music'://音乐
                $this->musicPath = $path;
                break;
            default:
                throw new \Exception('文件类型不存在');
        }
    }

    /**
     * 设置持续时间
     * @param int $duration
     */
    public function setDuration(int $duration)
    {
        $this->duration = $duration > 1 ? $duration : $this->duration;
    }

    /**
     * 设置输出文件
     * @param string $path
     */
    public function setOutput(string $path)
    {
        $this->output = $path ?? $this->output;
    }

    /**
     * 组建合成
     * @param string $type
     * @return bool
     * @throws ArException
     */
    public function build(string $type)
    {
        switch ($type) {
            case 'v2m':
                $shell = $this->mixVideo2Music();
                break;
            case 'i2m':
                $shell = $this->mixImgs2Music();
                break;
            default:
                throw new \Exception('合成类型不存在');
        }
        return $this->run($shell);
    }

    /**
     * 视频&音乐合成
     * @return string
     */
    private function mixVideo2Music()
    {
        // "ffmpeg -y -i a.mp4 -i a.mp3 -filter_complex \"[0:a]volume=0.8[a0]; [1:a]volume=0.8[a1]; [a0][a1]amix=inputs=2[a]\" -map 0:v -map \"[a]\" -c:v copy -c:a aac -shortest -strict -2 output.mp4;";
        $v = 0;
        $m = 1;
        $this->start = 'ffmpeg -y';
        $this->_video($v);
        $this->_music($m);

        $this->filter_complex = " -filter_complex \"" . $this->filter_complex . "  [a{$v}][a{$m}]amix=inputs=2[a]\"";
        $this->end = " -map 0:v -map \"[a]\" -c:v copy -c:a aac -t {$this->duration} -shortest -strict -2";
        $this->output = " output.mp4;";
        return $this->start . $this->filter_complex . $this->end . $this->output;
    }

    /**
     * 图片&音乐合成
     * @return string
     */
    private function mixImgs2Music()
    {
        //"ffmpeg -y -i a.mp3 -loop 1 -framerate 1  -i img/%d.png -c:v libx264 -r 30 -t 60 -s 200*200 -pix_fmt yuv420p -strict -2 out.mp4";
        $this->start = 'ffmpeg -y';
        $this->_music();
        $this->_imgs();

        $this->end = " -c:v libx264 -t {$this->duration} -strict -2";
        $this->output = " output.mp4;";
        return $this->start . $this->end . $this->output;
    }

    private function _video(int $times = 0, float $volume = 0.8)
    {
        $this->start .= " -i {$this->videoPath}";
        $this->filter_complex .= " [{$times}:a]volume={$volume}[a{$times}];";
    }
    private function _imgs(int $isLoop = 1, float $framerate = 1, int $rap = 30, string $size = '200*200')
    {
        $this->start .= " -loop {$isLoop} -framerate {$framerate} -i {$this->imagePath} -pix_fmt yuv420p -s {$size} -r {$rap}";
    }
    private function _music(int $times = 1, float $volume = 0.8)
    {
        $this->start .= " -i {$this->musicPath}";
        $this->filter_complex .= " [{$times}:a]volume={$volume}[a{$times}];";
    }

    /**
     * 运行
     * @param string $shell
     * @return bool
     */
    private function run(string $shell = '')
    {
        exec($shell, $output, $return);//执行命令
        return $return ? false : true;
    }
}

