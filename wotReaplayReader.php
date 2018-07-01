<?php
/**
 * @author hirokun ‎02.12.‎2017
 */
class wotReaplayReader {
	private $path;
	private $fd;
	private $bytes_readed = 0;

	/**
	 * Reads wotReplay file
	 * @param string $File_Path
	 * @return number
	 */
	public function __construct($path) {
		if (is_file($path)) {
			$this->path = $path;

			$this->fd = fopen($this->path, 'rb');
			if ($this->fd !== false) {

				fseek($this->fd, 8);
				$this->bytes_readed += 8;

				return true;
			}
		}

		return false;
	}

	public function __destruct() {
		fclose($this->fd);
	}

	/**
	 * Reads string from wotReplay file
	 * @return string
	 */
	public function read() {
		$len = $this->get_len();							// Определение длины строки
		if ($len > 0) {
			$ret = '';

			do {
				$str_read = fread($this->fd, $len);			// Чтение строки

				if ($str_read === false || feof($this->fd)) return false;

				$ret .= $str_read;

				$read_length = strlen($str_read);
				$this->bytes_readed += $read_length;
				$len = $len - $read_length;
			} while ($len > 0);

			return $ret;
		}

		return false;
	} // wrl_read_str

	/**
	 * Calcs wotReplay file's string length
	 * @param resource $fl
	 * @return number
	 */
	private function get_len() {
		$len_1 = fread($this->fd, 1);
		$len_2 = fread($this->fd, 1);
		$len_3 = fread($this->fd, 1);
		$len_4 = fread($this->fd, 1);
		$this->bytes_readed += 4;

		return hexdec(bin2hex($len_4).bin2hex($len_3).bin2hex($len_2).bin2hex($len_1));
	} // wrl_get_len

	public function read_length() {
		return $this->bytes_readed;
	}
}
