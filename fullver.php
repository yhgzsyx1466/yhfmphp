<?php

namespace Yhpfmphp;

define('yh_root', $_SERVER['DOCUMENT_ROOT'] . '/');
/**
 * Class FullVer
 * @package Yhpfmphp
 * @author 熙鹤
 * @version 1.0.1
 */
class FullVer
{
    /**
     * Get a info of the version of PHP.
     */
    public function php_get_version()
    {
        return PHP_VERSION;
    }
    /**
     * 删除文件,但会备份一份原文件(可选)
     * @param string $file_path 文件路径
     * @param bool $backfile 是否备份原文件(默认备份)
     */
    public function file_unlink($file_path, $backfile = true)
    {
        if (file_exists($file_path)) {
            if ($backfile) {
                $temp_file = md5(uniqid()) . '_' . basename($file_path);
                if (copy($file_path, yh_root . 'files_temp/' . $temp_file)) {
                    if (unlink($file_path)) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                if (unlink($file_path)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }
    /**
     * 复制文件夹，包含其中的文件
     * @param string $source 源文件夹路径
     * @param string $destination 目标文件夹路径
     */
    public function file_dir_copy($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }

        if (!is_dir($destination)) {
            if (!mkdir($destination, 0777, true)) {
                return false;
            }
        }
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                $source_path = $source . '/' . $file;
                $destination_path = $destination . '/' . $file;
                if (is_dir($source_path)) {
                    if (!$this->file_dir_copy($source_path, $destination_path)) {
                        return false;
                    }
                } elseif (is_file($source_path)) {
                    if (!copy($source_path, $destination_path)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
    /**
     * 删除文件夹,但会备份一份原文件夹(可选)
     * @param string $dir 文件夹路径
     * @param bool $backfile 是否备份原文件夹(默认备份)
     */
    public function file_dic_unlink($dir, $backfile = true)
    {
        if (!is_dir($dir)) {
            return false;
        }
        $empty = true;
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                $empty = false;
                break;
            }
        }
        if (!$empty && $backfile) {
            $temp_file = md5(uniqid()) . '_' . basename($dir);
            $backup_path = yh_root . 'files_temp/' . $temp_file;
            if (!$this->file_dir_copy($dir, $backup_path)) {
                return false;
            }
        }
        if (!$empty) {
            foreach ($files as $file) {
                if ($file !== "." && $file !== "..") {
                    $path = $dir . '/' . $file;
                    if (is_dir($path)) {
                        if (!$this->file_dic_unlink($path, false)) {
                            return false;
                        }
                    } elseif (is_file($path)) {
                        if (!unlink($path)) {
                            return false;
                        }
                    }
                }
            }
        }
        if (!rmdir($dir)) {
            return false;
        }
        return true;
    }
    /**
     * 获取请求方式
     */
    public function request_ways_get()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    /**
     * 检查请求方式是否合法
     * @param array $allowways 允许的请求方式
     */
    public function request_ways_check($allowways = ['GET', 'POST'])
    {
        $request_ways = $this->request_ways_get();
        if (in_array($request_ways, $allowways)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 获取POST请求中原始的JSON格式数据
     */
    public function request_post_data_get()
    {
        $data_y = file_get_contents('php://input');
        return json_decode($data_y, true);
    }
    /**
     * 获取微云笔记的内容
     * @param string $note_id 笔记ID
     * @param bool $ssl 是否开启ssl验证
     * @return string 返回笔记内容或"null"
     */
    public function request_weiyun_content_get($note_id, $ssl = true)
    {
        function unicodeDecode($unicode_str){
            $json = '{"str":"'.$unicode_str.'"}';
            $arr = json_decode($json,true);
            if(empty($arr)) return '';
            return $arr['str'];
        }
        $url = 'https://share.weiyun.com/' . $note_id;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (!$ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $html = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'null';
        } else {
            curl_close($ch);
            $pattern = '/"html_content":"((?:[^"\\\]++|\\\.)*)"/';
            preg_match($pattern, $html, $matches);
            if (isset($matches[1])) {
                $html_content = $matches[1];
                $convertedString = unicodeDecode($html_content);
                $convertedString2 = strip_tags($convertedString);
                return $convertedString2;
            } else {
                return "null";
            }
        }
    }
}
