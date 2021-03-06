<script type="text/javascript">
	var ress = new Array(<?=$parse['metal'] ?>, <?=$parse['crystal'] ?>, <?=$parse['deuterium'] ?>);
	var max = new Array(<?=$parse['metal_m'] ?>,<?=$parse['crystal_m'] ?>,<?=$parse['deuterium_m'] ?>);
	var production = new Array(<?=($parse['metal_ph'] / 3600) ?>, <?=($parse['crystal_ph'] / 3600) ?>, <?=($parse['deuterium_ph'] / 3600) ?>);
	timeouts['res_count'] = window.setInterval(XNova.updateResources, 1000);
	var serverTime = <?=$parse['time'] ?>000 - Djs + (timezone + <?=(date("Z") / 1800) ?>) * 1800000;
</script>
<form action="" name="ress" id="ress" style="display:none">
	<input type="hidden" id="metall" value="0">
	<input type="hidden" id="crystall" value="0">
	<input type="hidden" id="deuterium" value="0">
	<input type="hidden" id="bmetall" value="0">
	<input type="hidden" id="bcrystall" value="0">
	<input type="hidden" id="bdeuterium" value="0">
</form>
<div class="row topnav">
	<? if (\Xcms\Core::getConfig('showPlanetListSelect', 0) == 1): ?>
		<div class="col-md-2 hidden-xs col-sm-12">
			<div class="separator visible-sm"></div>
			<select style="width:100%;" onChange="load(this.options[this.selectedIndex].value);"><?=$parse['planetlist'] ?></select>
			<div class="separator visible-sm"></div>
		</div>
	<? endif; ?>
	<div class="col-md-<?=(\Xcms\Core::getConfig('showPlanetListSelect', 0) == 1 ? '5' : '6') ?> col-sm-6">
		<div class="row">
			<div class="col-xs-4 text-center">
				<span onclick="showWindow('<?=_getText('tech', 1) ?>', '?set=infos&gid=1&ajax&popup', 600)" class="tooltip hidden-xs" data-tooltip-content='<table width=150><tr><td width=30%>КПД:</td><td align=right><?=$parse['metal_mp'] ?>%</td></tr><tr><td>В час:</td><td align=right><?=\Xcms\Strings::pretty_number($parse['metal_ph']) ?></td></tr><tr><td>День:</td><td align=right><?=\Xcms\Strings::pretty_number($parse['metal_ph'] * 24) ?></td></tr></table>'><span class="sprite skin_metall"></span><br></span>
				<font color="#FFFF00">Металл</font><br>
				<div title="Количество ресурса на планете"><div id="met">-</div></div>
				<span title="Максимальная вместимость хранилищ" class="hidden-xs"><?=$parse['metal_max'] ?></span>
			</div>
			<div class="col-xs-4 text-center">
				<span onclick="showWindow('<?=_getText('tech', 2) ?>', '?set=infos&gid=2&ajax&popup', 600)" class="tooltip hidden-xs" data-tooltip-content='<table width=150><tr><td width=30%>КПД:</td><td align=right><?=$parse['crystal_mp'] ?>%</td></tr><tr><td>В час:</td><td align=right><?=\Xcms\Strings::pretty_number($parse['crystal_ph']) ?></td></tr><tr><td>День:</td><td align=right><?=\Xcms\Strings::pretty_number($parse['crystal_ph'] * 24) ?></td></tr></table>'><span class="sprite skin_kristall"></span><br></span>
				<font color="#FFFF00">Кристалл</font><br>
				<div title="Количество ресурса на планете"><div id="cry">-</div></div>
				<span title="Максимальная вместимость хранилищ" class="hidden-xs"><?=$parse['crystal_max'] ?></span>
			</div>
			<div class="col-xs-4 text-center">
				<span onclick="showWindow('<?=_getText('tech', 3) ?>', '?set=infos&gid=3&ajax&popup', 600)" class="tooltip hidden-xs" data-tooltip-content='<table width=150><tr><td width=30%>КПД:</td><td align=right><?=$parse['deuterium_mp'] ?>%</td></tr><tr><td>В час:</td><td align=right><?=\Xcms\Strings::pretty_number($parse['deuterium_ph']) ?></td></tr><tr><td>День:</td><td align=right><?=\Xcms\Strings::pretty_number($parse['deuterium_ph'] * 24) ?></td></tr></table>'><span class="sprite skin_deuterium"></span><br></span>
				<font color="#FFFF00">Дейтерий</font><br>
				<div title="Количество ресурса на планете"><div id="deu">-</div></div>
				<span title="Максимальная вместимость хранилищ" class="hidden-xs"><?=$parse['deuterium_max'] ?></span>
			</div>
		</div>
	</div>
	<div class="col-md-<?=(\Xcms\Core::getConfig('showPlanetListSelect', 0) == 1 ? '5' : '6') ?> col-sm-6">
		<div class="row">
			<div class="col-xs-4 text-center">
				<span onclick="showWindow('<?=_getText('tech', 4) ?>', '?set=infos&gid=4&ajax&popup', 600)" title="<?=_getText('tech', 4) ?>" class="hidden-xs"><span class="sprite skin_energie"></span><br></span>
				<font color="#FFFF00">Энергия</font><br>
				<div title="Энергетический баланс"><?=$parse['energy_total'] ?></div>
				<span title="Выработка энергии" class="hidden-xs"><font color="#00ff00"><?=$parse['energy_max'] ?></font></span>
			</div>
			<div class="col-xs-4 text-center">
				<span class="tooltip hidden-xs" data-tooltip-content='<center>Вместимость:<br><?=$parse['ak'] ?></center>'>
					<? if ($parse['energy_ak'] > 0 && $parse['energy_ak'] < 100): ?>
						<img src="<?=RPATH ?>images/batt.php?p=<?=$parse['energy_ak'] ?>" width="42" alt="">
					<? else: ?>
						<span class="sprite skin_batt<?=$parse['energy_ak'] ?>"></span>
					<? endif; ?>
					<br>
				</span>
				<font color="#FFFF00">Заряд</font><br>
				<?=$parse['energy_ak'] ?>%<br>
			</div>
			<div class="col-xs-4 text-center">
				<a href="?set=infokredits" class="tooltip hidden-xs" data-tooltip-content='
				<table width=550>
				<tr>
				<? foreach ($parse['officiers'] AS $oId => $oTime): ?>
					<td align="center" width="14%">
						<?=_getText('tech', $oId) ?>
						<div class="separator"></div>
						<span class="officier of<?=$oId ?><?=($oTime > time() ? '_ikon' : '') ?>"></span>
					</td>
				<? endforeach; ?>
				</tr>
				<tr>
				<? foreach ($parse['officiers'] AS $oId => $oTime): ?>
					<td align="center">
					<? if ($oTime > time()): ?>
						Нанят до <font color=lime><?=datezone("d.m.Y H:i", $oTime) ?></font>
					<? else: ?>
						<font color=lime>Не нанят</font>
					<? endif; ?>
					</td>
				<? endforeach; ?>
				</tr></table>'><span class="sprite skin_kredits"></span><br></a>
				<font color="#FFFF00">Кредиты</font><br>
				<?=$parse['credits'] ?><br>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$("#met").html(XNova.format(<?=$parse['metal'] ?>));
	$("#cry").html(XNova.format(<?=$parse['crystal'] ?>));
	$("#deu").html(XNova.format(<?=$parse['deuterium'] ?>));
	$(document).ready(XNova.updateResources());
</script>