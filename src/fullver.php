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
        function unicodeDecode($unicode_str)
        {
            $json = '{"str":"' . $unicode_str . '"}';
            $arr = json_decode($json, true);
            if (empty($arr)) return '';
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
    /**
     * 去除字符串中的空格
     * @param string $str 待处理的字符串
     */
    public function str_remove_white($str)
    {
        return preg_replace('/\s+/', '', $str);
    }
    /**
     * 生成一个复数乘法表达式
     * @param int $real 实部
     * @param int $imag 虚部
     * @param int $scalar 乘数
     * @param int $decimalPlaces 保留小数点后的位数
     * @return string 返回一个复数乘法表达式
     */
    public function number_multiply_complex($real, $imaginary, $scalar, $decimalPlaces)
    {
        $resultReal = $real * $scalar;
        $resultImaginary = $imaginary * $scalar;
        $resultReal = round($resultReal, $decimalPlaces);
        $resultImaginary = round($resultImaginary, $decimalPlaces);
        $result = $resultReal;
        if ($resultImaginary > 0) {
            $result .= " + " . $resultImaginary . "i";
        } elseif ($resultImaginary < 0) {
            $result .= " - " . abs($resultImaginary) . "i";
        }

        return $result;
    }
    /**
     * 生成一个复数除法表达式
     * @param int $a 实数
     * @param int $b 实部
     * @param int $c 虚部
     * @param int $decimalPlaces 保留小数点后的位数
     * @return string 返回一个复数除法表达式
     */
    public function number_divide_complex($a, $b, $c, $decimalPlaces)
    {
        $denominator = $b * $b + $c * $c;
        $resultReal = $a * $b / $denominator;
        $resultImaginary = - ($a * $c) / $denominator;
        $resultReal = round($resultReal, $decimalPlaces);
        $resultImaginary = round($resultImaginary, $decimalPlaces);
        $imaginarySign = ($resultImaginary < 0) ? " - " : " + ";
        $resultImaginary = abs($resultImaginary);
        return $resultReal . $imaginarySign . $resultImaginary . "i";
    }
    /**
     * 生成一个复数加法表达式
     * @param int $real 实数
     * @param int $realPart 实部
     * @param int $imaginaryPart 虚部
     * @param int $decimalPlaces 保留小数点后的位数
     * @return string 返回一个复数加法表达式
     */
    public function number_addrealandcomplex($real, $realPart, $imaginaryPart, $decimalPlaces)
    {
        $resultReal = $real + $realPart;
        $resultImaginary = $imaginaryPart;
        $resultReal = round($resultReal, $decimalPlaces);
        $resultImaginary = round($resultImaginary, $decimalPlaces);
        return $resultReal . " + " . $resultImaginary . "i";
    }
    /**
     * 生成一个复数减法表达式
     * @param int $real 实数
     * @param int $realPart 实部
     * @param int $imaginaryPart 虚部
     * @param int $decimalPlaces 保留小数点后的位数
     * @return string 返回一个复数减法表达式    
     */
    public function number_subtractrealandcomplex($real, $realPart, $imaginaryPart, $decimalPlaces)
    {
        $resultReal = $real - $realPart;
        $resultImaginary = -$imaginaryPart;
        $resultReal = round($resultReal, $decimalPlaces);
        $resultImaginary = round($resultImaginary, $decimalPlaces);
        $result = $resultReal;
        if ($resultImaginary > 0) {
            $result .= " + " . $resultImaginary . "i";
        } elseif ($resultImaginary < 0) {
            $result .= " - " . abs($resultImaginary) . "i";
        }

        return $result;
    }
    /**
     * 去除字符串中的不匹配括号
     * @param string $expression 待处理的字符串
     * @return string 返回去除不匹配括号后的字符串
     */
    public function str_remove_unmatchbrackets($expression)
    {
        $stack = [];
        $toRemove = [];
        for ($i = 0; $i < strlen($expression); $i++) {
            $char = $expression[$i];
            if ($char == '(') {
                array_push($stack, $i);
            } else if ($char == ')') {
                if (empty($stack)) {
                    array_push($toRemove, $i);
                } else {
                    array_pop($stack);
                }
            }
        }
        while (!empty($stack)) {
            array_push($toRemove, array_pop($stack));
        }
        $result = '';
        for ($i = 0; $i < strlen($expression); $i++) {
            if (!in_array($i, $toRemove)) {
                $result .= $expression[$i];
            }
        }
        return $result;
    }
    /**
     * 生成一个数学式子,可作验证用
     * @param int $decimalPlaces2 保留小数点后的位数
     * @return array 返回一个数组,包含问题和答案['problem', 'answer']
     */
    public function auth_math_formula($decimalPlaces2)
    {
        function math_eval($expression)
        {
            $result = @eval("return $expression;");
            return $result;
        }
        function math_random($min, $max, $decimals = 0)
        {
            return round(mt_rand($min * pow(10, $decimals), $max * pow(10, $decimals)) / pow(10, $decimals), $decimals);
        }
        $operators = ['+', '-', '*', '/'];
        $specialOperators = ['sqrt', '^', 'sin', 'cos', 'tan', 'log'];
        $num1 = math_random(0, 100, 2);
        $num2 = math_random(0, 100, 2);
        $num3 = math_random(0, 100, 2);
        $num4 = math_random(0, 100, 2);
        $operator1 = $operators[array_rand($operators)];
        $operator2 = $operators[array_rand($operators)];
        $operator3 = $operators[array_rand($operators)];
        $useSpecialOperator = mt_rand(0, 1) > 0.5;
        $specialOperator = $specialOperators[array_rand($specialOperators)];
        $specialOperand = $num1;
        $problem = '';
        $evalProblem = '';

        if ($useSpecialOperator) {
            if ($specialOperator === 'sqrt') {
                $specialOperand = $num2;
                $rootNum = min($specialOperand, 10);
                $problem = "(√" . $rootNum . ") $operator1 $num3 $operator2 $num4";
                $evalProblem = "sqrt($rootNum) $operator1 $num3 $operator2 $num4";
            } else if (in_array($specialOperator, ['sin', 'cos', 'tan'])) {
                $angle = math_random(0, 2 * M_PI, 2);
                $problem = "$specialOperator($angle) $operator1 $num2 $operator2 $num3 $operator3 $num4";
                $evalProblem = "$specialOperator($angle) $operator1 $num2 $operator2 $num3 $operator3 $num4";
            } else if ($specialOperator === 'log') {
                $logNum = math_random(1, 99, 2);
                $problem = "log($logNum) $operator1 $num2 $operator2 $num3 $operator3 $num4";
                $evalProblem = "log($logNum) $operator1 $num2 $operator2 $num3 $operator3 $num4";
            } else if ($specialOperator === '^') {
                $exponent = min($num3, 10);
                $problem = "$num1 $operator1 ($num2**$exponent) $operator2 $num4";
                $evalProblem = "$num1 $operator1 pow($num2, $exponent) $operator2 $num4";
            }
        } else {
            $problem = "$num1 $operator1 $num2 $operator2 $num3 $operator3 $num4)";
            $evalProblem = "$num1 $operator1 $num2 $operator2 $num3 $operator3 $num4";
        }
        $evalProblem = "($evalProblem)";
        $problem = $this->str_remove_unmatchbrackets($problem);
        if(strpos($problem, '(') !== false && strpos($problem, ')') !== false){
            $problem = "[$problem]";
        }else{
            $problem = "($problem)";
        }
        $realPart = math_random(0, 10, 2);
        $imagPart = math_random(0, 10, 2);
        $complexOperator = $operators[array_rand($operators)];
        $answer_b = math_eval($evalProblem);
        switch ($complexOperator) {
            case '+':
                $answer = $this->number_addrealandcomplex($answer_b, $realPart, $imagPart, $decimalPlaces2);
                break;
            case '-':
                $answer = $this->number_subtractrealandcomplex($answer_b, $realPart, $imagPart, $decimalPlaces2);
                break;
            case '*':
                $answer = $this->number_multiply_complex($realPart, $imagPart, $answer_b, $decimalPlaces2);
                break;
            case '/':
                $answer = $this->number_divide_complex($answer_b, $realPart, $imagPart, $decimalPlaces2);
                break;
            default:
                $answer = 'Error';
                break;
        }
        $roundedAnswer = ($answer);
        $problem .= "$complexOperator ($realPart + $imagPart" . 'i)';
        return [
            'problem' => $problem,
            'answer' => $this->str_remove_white($roundedAnswer . '')
        ];
    }
}
