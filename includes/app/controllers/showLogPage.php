<?php

namespace Xnova\controllers;

use Xcms\core;
use Xcms\db;
use Xcms\request;
use Xnova\User;
use Xnova\pageHelper;

class showLogPage extends pageHelper
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		if (user::get()->isAuthorized() && !isset($_GET['id']))
		{
			$message = "";
		
			if (isset($_GET['mysql']) && $_GET['mysql'] == 'new')
			{
				if (!$_POST['title'])
					$message = '<h1><font color=red>Введите название для боевого отчёта.</h1>';
				elseif (!$_POST['code'])
					$message = '<h1><font color=red>Введите ID боевого отчёта.</h1>';
				else
				{
					$key = substr($_POST['code'], 0, 32);
					$id = substr($_POST['code'], 32, (mb_strlen($_POST['code'], 'UTF-8') - 32));
		
					if (md5('xnovasuka' . $id) != $key)
						$this->message('Не правильный ключ', 'Ошибка', '', 0, false);
					else
					{
						$log = db::query("SELECT * FROM game_rw WHERE `id` = '" . $id . "';", true);
						if (isset($log['id']))
						{
							$user_list = json_decode($log['id_users']);
		
							if ($user_list[0] == user::get()->data['id'] && $log['no_contact'] == 1)
							{
								$SaveLog = "Контакт с флотом потерян.<br>(Флот был уничтожен в первой волне атаки.)";
							}
							else
							{
								$SaveLog = json_decode($log['raport'], true);
		
								foreach ($SaveLog[0]['rw'] as $round => $data1)
								{
									unset($SaveLog[0]['rw'][$round]['logA']);
									unset($SaveLog[0]['rw'][$round]['logD']);
								}
		
								$SaveLog = json_encode($SaveLog);
							}
		
							db::query("INSERT INTO game_savelog (`user`, `title`, `log`) VALUES ('" . user::get()->data['id'] . "', '" . addslashes(htmlspecialchars($_POST['title'])) . "', '" . addslashes($SaveLog) . "')");
							$message = 'Боевой отчёт успешно сохранён.';
						}
						else
							$message = 'Боевой отчёт не найден в базе';
					}
				}
				$this->message($message, "Логовница", "?set=log", 2);
			}
		
			$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';
		
			switch ($mode)
			{
				case 'new':
		
					$html = "<table class=\"table\"><tr><td class=\"c\"><h1>Сохранение боевого доклада</h1></td></tr>";
					$html .= "<tr><th><form action=?set=log&mysql=new method=POST>";
					$html .= "Название:<br>";
					$html .= "<input type=text name=title size=50 maxlength=100><br>";
					$html .= "ID боевого доклада:<br>";
					$html .= "<input type=text name=code size=50 maxlength=40 " . ((isset($_GET['save'])) ? 'value="' . $_GET['save'] . '"' : '') . ">";
					$html .= "<br>";
					$html .= "<br><input type=submit value='Сохранить'>";
					$html .= "</form></th></tr></table>";

					$this->setTitle('Логовница');
					$this->setContent($html);
					$this->showTopPanel(false);
					$this->display();
		
					break;
		
				case 'delete':
		
					if (isset($_GET['id_l']))
					{
						$id = intval($_GET['id_l']);
		
						$sql = db::query("SELECT * FROM game_savelog WHERE id = '" . $id . "' ");
						$raportrow = db::fetch_assoc($sql);
		
						if (user::get()->data['id'] == $raportrow['user'])
						{
							db::query("DELETE FROM game_savelog WHERE `id` = " . $id . " ");
							request::redirectTo("?set=log");
						}
						else
							$this->message("Ошибка удаления.", "Логовница", "?set=log", 1);
					}
		
					break;
		
				default:
		
					$ksql = db::query("SELECT `id`, `user`, `title` FROM game_savelog WHERE `user` = '" . user::get()->data['id'] . "' ");
		
					$html  = "<table class=\"table\">";
					$html .= "<tr><th colspan=4>Логовница</th></tr>";
					$html .= "<tr>";
					$html .= "<td class=c colspan=4>Ваши сохранённые логи</td>";
					$html .= "</tr>";
					$html .= "<tr><td class=c>№</td><td class=c>Название</td><td class=c>Ссылка</td><td class=c>Управление логом</td></tr>";
					$i = 0;
					while ($krow = db::fetch($ksql))
					{
						$i++;
						$html .= "<tr><td class=\"b center\">" . $i . "</td><td class=\"b center\">" . $krow['title'] . "</td><td class=\"b center\"><a href=?set=log&id=" . $krow['id'] . " ".(core::getConfig('openRaportInNewWindow', 0) == 1 ? 'target="_blank"' : '').">Открыть</a></td><td class=\"b center\"><a href='?set=log&mode=delete&id_l=" . $krow['id'] . "'>Удалить лог</a></td></tr>";
					}
					if ($i == 0)
						$html .= "<tr align=center><td class=\"b center\" colspan=4>У вас пока нет сохранённых логов.</td></tr>";
		
					$html .= "<tr><td class=c colspan=4><a href=?set=log&mode=new>Создать новый лог боя</a></td></tr></table>";

					$this->setTitle('Логовница');
					$this->setContent($html);
					$this->showTopPanel(false);
					$this->display();
			}
		}
		
		if (isset($_GET['id']))
		{
			$html = '';

			$raportrow = db::query("SELECT * FROM game_savelog WHERE id = '" . intval($_GET['id']) . "' ", true);
		
			if (isset($raportrow['id']))
			{
				include(ROOT_DIR.APP_PATH."functions/formatCombatReport.php");

				$result = json_decode($raportrow['log'], true);

				if (!core::getConfig('openRaportInNewWindow', 0) && user::get()->isAuthorized())
				{
					if (!is_array($result) || ($raportrow['user'] == 0 && $result[0]['time'] > (time() - 7200)))
						echo "<center>Данный лог боя пока недоступен для просмотра!</center>";
					else
					{
						$html .= formatCombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6])['html'];
						$html .= '<script>$(function(){$(\'#raportRaw\').multiAccordion({active: ['.(count($result[0]['rw']) - 1).']})});</script>';
					}

					$this->setTitle('Боевой доклад');
					$this->setContent($html);
					$this->showTopPanel(false);
					$this->display();
				}
				else
				{
					$html = "<html><head><title>" . stripslashes($raportrow["title"]) . "</title>";
					$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".DPATH."report_v2.css\">";
					$html .= "</head><body>";
					$html .= "<table width=\"99%\"><tr><td>";

					if (!is_array($result) || ($raportrow['user'] == 0 && $result[0]['time'] > (time() - 7200) && !user::get()->isAdmin()))
						echo "<center>Данный лог боя пока недоступен для просмотра!</center>";
					else
					{
						$html .= formatCombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6])['html'];
					}
		
					$html .= "</td></tr></table>";
					$html .= file_get_contents(ROOT_DIR.'/template/main/block/counter.php');
					$html .= "</body></html>";
				}
			}
			else
			{
				if (!core::getConfig('openRaportInNewWindow', 0) && user::get()->isAuthorized())
					$this->message('Запрашиваемого лога не существует в базе данных');
				else
				{
					$html = "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"/xnsim/report.css\">";
					$html .= "</head><body><center>Запрашиваемого лога не существует в базе данных</center></body></html>";
				}
			}
		
			echo $html;
		}
	}
}

?>