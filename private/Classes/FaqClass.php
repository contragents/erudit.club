<?php


class Faq
{
    const RULES = [
        T::EN_LANG =>
        <<<EN
<h2 id="nav1">Об игре</h2>
                            <p>Эрудит на английском &mdash; настольная игра со словами, в которую могут играть от 2 до 4 человек, выкладывая слова из имеющихся у них букв на поле размером 15x15.</p>
                            <div class="fon-right">
                                <h2 id="nav2">Игровое поле</h2>
                                <p>Игровое поле состоит из 15х15, то есть 225 квадратов, на которые участники игры выкладывают буквы, составляя тем самым&nbsp;слова. В начале игры каждый игрок получает 7 случайных букв. 
                                <p>На середину игрового поля выкладывается первое&nbsp;слово. К этому слову по возможности, нужно приставить осташиеся буквы так, чтобы на пересечении получились новые слова.</p>
                                <p>Затем следующий игрок должен выставить свои&nbsp;буквы&nbsp;&laquo;на пересечение&raquo; или приставить их к уже составленным словам.</p>
                                <p>Слова&nbsp;выкладываются либо слева направо, либо сверху вниз.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav3">Словарь</h2>
                                <p>Разрешается использовать все&nbsp;слова, приведенные в кэмбриджском англо-русском словаре (https://dictionary.cambridge.org/ru/ ), включая наиболее <a href="#" onclick="$('#abbr').css({ display: 'block' });return false;" style="cursor: pointer;" title="ad AGM AIDS ATM BA BBC BSc BSE CCTV CD CEO CFC Corp dab DIY DNA DVD EFL ELT er ESL FA FAQ FM GCSE GDP GMO GMT GNP GP GPS HIV HQ ICT IOU IPA IQ ISP it ITV IVF JP JPEG LAN LCD LPG MA MBA MEP MP MPV MRI MRSA Ms MSc MTV NATO OAP PC PDA PE pin POW PR pt QC ram RSI SARS SASE SATNAV SGML SIDS SMS SPF SUV TB TEFL TESOL TV UFO UK USA VAT VCR VDU VIP WC WMD www XML">употребительные аббревиатуры</a>.<span id="abbr" style="display:none;">ad AGM AIDS ATM BA BBC BSc BSE CCTV CD CEO CFC Corp dab DIY DNA DVD EFL ELT er ESL FA FAQ FM GCSE GDP GMO GMT GNP GP GPS HIV HQ ICT IOU IPA IQ ISP it ITV IVF JP JPEG LAN LCD LPG MA MBA MEP MP MPV MRI MRSA Ms MSc MTV NATO OAP PC PDA PE pin POW PR pt QC ram RSI SARS SASE SATNAV SGML SIDS SMS SPF SUV TB TEFL TESOL TV UFO UK USA VAT VCR VDU VIP WC WMD www XML</span></p>
                                <p>Разрешено использовать только нарицательные имена существительные в единственном числе (либо во множественном при отсутствии у слова формы единственного числа).</p>
                                <p>Чтобы посмотреть, какие слова составили игроки в предыдущих ходах, а также узнать их значение и &laquo;стоимость&raquo;, кликните на кнопку <img src="/img/otjat/log2.svg" height="64"/></p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav4">Ход игры</h2>
                                <p>В начале игры каждому дается по 7 фишек. За один ход можно выложить несколько&nbsp;слов. Каждое новое&nbsp;слово&nbsp;должно соприкасаться (иметь общую букву или буквы) с ранее выложенными&nbsp;словами.&nbsp;Слова&nbsp;читаются только по горизонтали слева направо и по вертикали сверху вниз.</p>
                                <p>Первое выложенное&nbsp;слово&nbsp;должно проходить через центральную клетку.</p>
                                <p>
                                Отправить свою комбинацию можно, нажав кнопку <br /><img src="/img/otjat/otpravit2.svg" width="80%"/>
                                <br />
                                Если в данный момент ход не Ваш - кнопка станет неактивной <br /><img src="/img/inactive/otpravit2.svg" width="80%"/>
                                <br />
                                Если кнопка ОТПРАВИТЬ начала мигать красным - время Вашего хода заканчивается. Скорее отправляйте свою комбинацию!
                                </p>
                                <p>Если игрок не хочет или не может выложить ни одного слова, - он имеет право поменять любое количество своих букв, пропустив при этом ход.
                                <br /><img src="/img/otjat/pomenyat2.svg" width="80%"/>
                                </p>
                                <p>Любая последовательность букв по горизонтали и вертикали должна являться&nbsp;словом. Т.е. в игре не допускается появление на поле случайных буквосочетаний, не представляющих собою&nbsp;слов, соответствующих вышеприведенным критериям.</p>
                                <p>После каждого хода необходимо добрать новых букв до 7.</p>
                                <p>Если за ход игрок использовал все 7 букв, то ему начисляются дополнительные 15 очков.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav5">Распределение фишек и стоимость букв</h2>
                                <table cellpadding="10" cellspacing="10">
                                    <tbody>
                                        <tr>
                                            <th>Буква</th>
                                            <th>Кол-во</th>
                                            <th>Цена</th>
                                        </tr>
                                        <tr>
                                            <td><strong>*</strong></td>
                                            <td>10 шт.</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>A</td>
                                            <td>9 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>B</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>C</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>D</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>E</td>
                                            <td>12 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>F</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>G</td>
                                            <td>3 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>H</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>I</td>
                                            <td>9 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>J</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>K</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>L</td>
                                            <td>4 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>M</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>N</td>
                                            <td>6 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>O</td>
                                            <td>8 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>P</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Q</td>
                                            <td>1 шт.</td>
                                            <td>10 очков</td>
                                        </tr>
                                        <tr>
                                            <td>R</td>
                                            <td>6 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>S</td>
                                            <td>4 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>T</td>
                                            <td>6 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>U</td>
                                            <td>4 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>V</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>W</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>X</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Y</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Z</td>
                                            <td>1 шт.</td>
                                            <td>10 очков</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav6">Подсчет очков и бонусы</h2>
                                <p>Каждой букве присвоено количество очков от 1 до 10. Некоторые квадраты на доске раскрашены в разные цвета. Количество очков, получаемых игроком за выложенное слово, подсчитывается следующим образом:</p>
                                <ul>
                                    <li>Если квадрат под буквой бесцветен, добавляется количество очков, написанное на букве</li>
                                    <li>Если квадрат <span style="background-color:green;color:white;">зеленый</span> - количество очков <strong>буквы</strong> умножается на <strong>2</strong></li>
                                    <li>Если квадрат <span style="background-color:yellow;color:black;">желтый</span> - количество очков <strong>буквы</strong> умножается на <strong>3</strong></li>
                                    <li>Если квадрат <span style="background-color:blue;color:white;">синий</span> - количество очков всего <strong>слова</strong> умножается на <strong>2</strong></li>
                                    <li>Если квадрат <span style="background-color:red;color:white;">красный</span> - количество очков всего <strong>слова</strong> умножается на <strong>3</strong></li>
                                </ul>
                                <p>Если слово использует множители обоего типа, то в удвоении (утроении) очков слова учитывается удвоение (утроение) очков букв.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav7">Звёздочка</h2>
                                <p>Также, в наборе фишек присутствуют три звёздочки. Такая фишка может быть использована как любая буква на выбор игрока. Например, игрок может выставить слово &laquo;P*ONE&raquo;, где роль буквы &laquo;H&raquo; будет играть звездочка.</p>
                                <p>Как только игрок выставит на поле звездочку, игра сразу предложит выбрать заменяемую ею букву. При перестановке звездочки выбор буквы будет предлагаться вновь.</p>
                                <p>Звездочка приносит столько очков, сколько бы принесла буква, роль которой она играет.&nbsp;</p>
                                <h3>Повторное использование звёздочки&nbsp;</h3>
                                <p>Если у любого из игроков есть буква, которую заменяет звёздочка на игровом поле, то он может заменить эту звёздочку своей буквой и использовать полученную звёздочку для составления слова, но только в текущий ход. Забрать звёздочку с поля "про запас" себе нельзя.</p>
                            </div>
EN,

        T::RU_LANG =>
    <<<RU
<h2 id="nav1">Об игре</h2>
                            <p>Эрудит &mdash; настольная игра со словами, в которую могут играть от 2 до 4 человек, выкладывая слова из имеющихся у них букв на поле размером 15x15.</p>
                            <div class="fon-right">
                                <h2 id="nav2">Игровое поле</h2>
                                <p>Игровое поле состоит из 15х15, то есть 225 квадратов, на которые участники игры выкладывают буквы, составляя тем самым&nbsp;слова. В начале игры каждый игрок получает 7 случайных букв (всего их в игре 102). 
                                <p>На середину игрового поля выкладывается первое&nbsp;слово. К этому слову по возможности, нужно приставить осташиеся буквы так, чтобы на пересечении получились новые слова.</p>
                                <p>Затем следующий игрок должен выставить свои&nbsp;буквы&nbsp;&laquo;на пересечение&raquo; или приставить их к уже составленным словам.</p>
                                <p>Слова&nbsp;выкладываются либо слева направо, либо сверху вниз.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav3">Словарь</h2>
                                <p>Разрешается использовать все&nbsp;слова, приведенные в стандартном словаре языка за исключением&nbsp;слов, пишущихся с прописных букв, сокращений, и слов, которые пишутся через апостроф или дефис.</p>
                                <p>Разрешено использовать только нарицательные имена существительные в именительном падеже и единственном числе (либо во множественном при отсутствии у слова формы единственного числа, ЛИБО, если слово во множественном числе содержится в одном из словарей Игры - см. значение слова в меню ЛОГ).</p>
                                <p>Чтобы посмотреть, какие слова составили игроки в предыдущих ходах, а также узнать их значение и &laquo;стоимость&raquo;, кликните на кнопку <img src="/img/otjat/log2.svg" height="64"/></p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav4">Ход игры</h2>
                                <p>В начале игры каждому дается по 7 фишек. За один ход можно выложить несколько&nbsp;слов. Каждое новое&nbsp;слово&nbsp;должно соприкасаться (иметь общую букву или буквы) с ранее выложенными&nbsp;словами.&nbsp;Слова&nbsp;читаются только по горизонтали слева направо и по вертикали сверху вниз.</p>
                                <p>Первое выложенное&nbsp;слово&nbsp;должно проходить через центральную клетку.</p>
                                <p>
                                Отправить свою комбинацию можно, нажав кнопку <br /><img src="/img/otjat/otpravit2_ru.svg" width="80%"/>
                                <br />
                                Если в данный момент ход не Ваш - кнопка станет неактивной <br /><img src="/img/inactive/otpravit2_ru.svg" width="80%"/>
                                <br />
                                Если кнопка ОТПРАВИТЬ начала мигать красным - время Вашего хода заканчивается. Скорее отправляйте свою комбинацию!
                                </p>
                                <p>Если игрок не хочет или не может выложить ни одного слова, - он имеет право поменять любое количество своих букв, пропустив при этом ход.
                                <br /><img src="/img/otjat/pomenyat2_ru.svg" width="80%"/>
                                </p>
                                <p>Любая последовательность букв по горизонтали и вертикали должна являться&nbsp;словом. Т.е. в игре не допускается появление на поле случайных буквосочетаний, не представляющих собою&nbsp;слов, соответствующих вышеприведенным критериям.</p>
                                <p>После каждого хода необходимо добрать новых букв до 7.</p>
                                <p>Если за ход игрок использовал все 7 букв, то ему начисляются дополнительные 15 очков.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav5">Распределение фишек и стоимость букв</h2>
                                <table cellpadding="10" cellspacing="10">
                                    <tbody>
                                        <tr>
                                            <th>Буква</th>
                                            <th>Кол-во</th>
                                            <th>Цена</th>
                                        </tr>
                                        <tr>
                                            <td><strong>*</strong></td>
                                            <td>3 шт.</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>А</td>
                                            <td>8 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Б</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>В</td>
                                            <td>4 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Г</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Д</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Е</td>
                                            <td>9 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Ж</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>З</td>
                                            <td>2 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>И</td>
                                            <td>6 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Й</td>
                                            <td>1 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>К</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Л</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>М</td>
                                            <td>3 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Н</td>
                                            <td>5 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>О</td>
                                            <td>10 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>П</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Р</td>
                                            <td>5 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>С</td>
                                            <td>5 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>Т</td>
                                            <td>5 шт.</td>
                                            <td>1 очко</td>
                                        </tr>
                                        <tr>
                                            <td>У</td>
                                            <td>4 шт.</td>
                                            <td>2 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Ф</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Х</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ц</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ч</td>
                                            <td>1 шт.</td>
                                            <td>5 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ш</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Щ</td>
                                            <td>1 шт.</td>
                                            <td>10 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ъ</td>
                                            <td>1 шт.</td>
                                            <td>15 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ы</td>
                                            <td>2 шт.</td>
                                            <td>4 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Ь</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                        <tr>
                                            <td>Э</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Ю</td>
                                            <td>1 шт.</td>
                                            <td>8 очков</td>
                                        </tr>
                                        <tr>
                                            <td>Я</td>
                                            <td>2 шт.</td>
                                            <td>3 очка</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav6">Подсчет очков и бонусы</h2>
                                <p>Каждой букве присвоено количество очков от 1 до 10. Некоторые квадраты на доске раскрашены в разные цвета. Количество очков, получаемых игроком за выложенное слово, подсчитывается следующим образом:</p>
                                <ul>
                                    <li>Если квадрат под буквой бесцветен, добавляется количество очков, написанное на букве</li>
                                    <li>Если квадрат <span style="background-color:green;color:white;">зеленый</span> - количество очков <strong>буквы</strong> умножается на <strong>2</strong></li>
                                    <li>Если квадрат <span style="background-color:yellow;color:black;">желтый</span> - количество очков <strong>буквы</strong> умножается на <strong>3</strong></li>
                                    <li>Если квадрат <span style="background-color:blue;color:white;">синий</span> - количество очков всего <strong>слова</strong> умножается на <strong>2</strong></li>
                                    <li>Если квадрат <span style="background-color:red;color:white;">красный</span> - количество очков всего <strong>слова</strong> умножается на <strong>3</strong></li>
                                </ul>
                                <p>Если слово использует множители обоего типа, то в удвоении (утроении) очков слова учитывается удвоение (утроение) очков букв.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav7">Звёздочка</h2>
                                <p>Также, в наборе фишек присутствуют три звёздочки. Такая фишка может быть использована как любая буква на выбор игрока. Например, игрок может выставить слово &laquo;ТЕ*ЕФОН&raquo;, где роль буквы &laquo;Л&raquo; будет играть звездочка.</p>
                                <p>Как только игрок выставит на поле звездочку, игра сразу предложит выбрать заменяемую ею букву. При перестановке звездочки выбор буквы будет предлагаться вновь.</p>
                                <p>Звездочка приносит столько очков, сколько бы принесла буква, роль которой она играет.&nbsp;</p>
                                <h3>Повторное использование звёздочки&nbsp;</h3>
                                <p>Если у любого из игроков есть буква, которую заменяет звёздочка на игровом поле, то он может заменить эту звёздочку своей буквой и использовать полученную звёздочку для составления слова, но только в текущий ход. Забрать звёздочку с поля "про запас" себе нельзя.</p>
                            </div>
RU
    ];
    const RATING = [
        T::EN_LANG => 'Rating gain rules',
        T::RU_LANG => 'Правила расчета рейтинга',
    ];
    const REWARDS = [
        T::EN_LANG => <<<EN
За определенные достижения (рекорды) игроки получают награды.
<br><br>
Награды игрока отражаются в разделе "СТАТИСТИКА" в следующих
номинациях: золото/серебро/бронза/камень.
<br><br>
При полученнии карточки-награды игроку также начисляется бонус монетами SUDOKU {{sudoku_icon}}<br> 
Использовать монеты можно в специальном режиме игры "НА МОНЕТЫ", можно пополнять внутриигровой кошелек, 
а также выводить монеты из игры - подробнее читайте во вкладке "ИГРА НА МОНЕТЫ"
<br><br>
<h2>Список достижений и их стоимость в монетах</h2>

<table class="table table-dark table-transp">
<thead>
МЕСТО В РЕЙТИНГЕ
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
ТОП 1
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
ТОП 2
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
ТОП 3
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
10 лучших
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
ОЧКИ ЗА ИГРУ
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
ОЧКИ ЗА ХОД
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
ОЧКИ ЗА СЛОВО
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
САМОЕ ДЛИННОЕ СЛОВО
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
СЫГРАНО ПАРТИЙ
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
ПРИГЛАШЕННЫЕ ДРУЗЬЯ (РЕФЕРАЛЫ)
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

Пока рекорд одного игрока не был перебит другим игроком, карточка-награда отражается у такого игрока во вкладке "АКТИВНЫЕ НАГРАДЫ"
раздела "СТАТИСТИКА".<br><br>
Каждая "АКТИВНАЯ НАГРАДА" каждый час генерирует доплонительную
"прибыль" в монетах.<br><br>
Если рекорд был перебит другим игроком, то карточка-награда у
предыдущего владельца рекорда перемещается во вкладку "ПРОШЛЫЕ
НАГРАДЫ" и перестает приносить пассивный доход.<br><br>
Общее количество полученных монет (единовременные бонусы и
дополнительная прибыль) можно посмотреть в разделе "ПРОФИЛЬ" во
вкладке "КОШЕЛЕК" в поле "Баланс SUDOKU" и "Начислено бонусов" соответственно.<br><br>
При превышении собственного рекорда для достижений "СЫГРАНО
ПАРТИЙ" и "ПРИГЛАШЕННЫЕ ДРУЗЬЯ" игроку повторно не выдается новая
карточка-награда и не начисляются монеты повторно. Само значение рекорда
(число игр / количество друзей) обновляется на карточке-награде.<br><br>
Например, если игрок ранее получил достижение - "СЫГРАНО ПАРТИЙ"
(золото) за 10 000 игр, то при изменении количества игр у этого игрока на
значение 10 001 еще одна карточка-награда обладателю рекорда не выдается.<br>
EN,
        T::RU_LANG => <<<RU
За определенные достижения (рекорды) игроки получают награды.
<br><br>
Награды игрока отражаются в разделе "СТАТИСТИКА" в следующих
номинациях: золото/серебро/бронза/камень.
<br><br>
При полученнии карточки-награды игроку также начисляется бонус монетами SUDOKU {{sudoku_icon}}<br> 
Использовать монеты можно в специальном режиме игры "НА МОНЕТЫ", можно пополнять внутриигровой кошелек, 
а также выводить монеты из игры - подробнее читайте во вкладке "ИГРА НА МОНЕТЫ"
<br><br>
<h2>Список достижений и их стоимость в монетах</h2>

<table class="table table-dark table-transp">
<thead>
МЕСТО В РЕЙТИНГЕ
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
ТОП 1
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
ТОП 2
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
ТОП 3
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
10 лучших
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
ОЧКИ ЗА ИГРУ
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
ОЧКИ ЗА ХОД
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
ОЧКИ ЗА СЛОВО
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
САМОЕ ДЛИННОЕ СЛОВО
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
СЫГРАНО ПАРТИЙ
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

<table class="table table-dark table-transp">
<thead>
ПРИГЛАШЕННЫЕ ДРУЗЬЯ (РЕФЕРАЛЫ)
</thead>
<tr>
<td>
Тип
</td>
<td>
Название
</td>
<td>
Награда
</td>
<td>
Прибыль<br> в час
</td>
</tr>
<tr>
<td>
золото
</td>
<td>
РЕКОРД ГОДА
</td>
<td>
{{sudoku_icon}} {{gold_reward}}
</td>
<td>
{{sudoku_icon}} {{gold_income}}
</td>
</tr>
<tr>
<td>
серебро
</td>
<td>
РЕКОРД МЕСЯЦА
</td>
<td>
{{sudoku_icon}} {{silver_reward}}
</td>
<td>
{{sudoku_icon}} {{silver_income}}
</td>
</tr>
<tr>
<td>
бронза
</td>
<td>
РЕКОРД НЕДЕЛИ
</td>
<td>
{{sudoku_icon}} {{bronze_reward}}
</td>
<td>
{{sudoku_icon}} {{bronze_income}}
</td>
</tr>
<tr>
<td>
камень
</td>
<td>
РЕКОРД ДНЯ
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

Пока рекорд одного игрока не был перебит другим игроком, карточка-награда отражается у такого игрока во вкладке "АКТИВНЫЕ НАГРАДЫ"
раздела "СТАТИСТИКА".<br><br>
Каждая "АКТИВНАЯ НАГРАДА" каждый час генерирует доплонительную
"прибыль" в монетах.<br><br>
Если рекорд был перебит другим игроком, то карточка-награда у
предыдущего владельца рекорда перемещается во вкладку "ПРОШЛЫЕ
НАГРАДЫ" и перестает приносить пассивный доход.<br><br>
Общее количество полученных монет (единовременные бонусы и
дополнительная прибыль) можно посмотреть в разделе "ПРОФИЛЬ" во
вкладке "КОШЕЛЕК" в поле "Баланс SUDOKU" и "Начислено бонусов" соответственно.<br><br>
При превышении собственного рекорда для достижений "СЫГРАНО
ПАРТИЙ" и "ПРИГЛАШЕННЫЕ ДРУЗЬЯ" игроку повторно не выдается новая
карточка-награда и не начисляются монеты повторно. Само значение рекорда
(число игр / количество друзей) обновляется на карточке-награде.<br><br>
Например, если игрок ранее получил достижение - "СЫГРАНО ПАРТИЙ"
(золото) за 10 000 игр, то при изменении количества игр у этого игрока на
значение 10 001 еще одна карточка-награда обладателю рекорда не выдается.<br>
RU
    ];
    const COINS = [
        T::EN_LANG => 'Coins use rules',
        T::RU_LANG => 'Монеты в игре',
    ];
}