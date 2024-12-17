<?php


class Faq
{
    const RULES = [
        T::EN_LANG =>
        <<<EN
<h2 id="nav1">About the game</h2>
                            <p>Scrabble &mdash; a word board game that can be played by 2 to 4 people by building words from the letters they have on a 15x15 field.</p>
                            <div class="fon-right">
                                <h2 id="nav2">Playing board</h2>
                                <p>The game board consists of 15x15, i.e. 225 squares, on which the participants of the game put letters, thus making words. At the beginning of the game each player receives 7 random letters. 
                                <p>The first word is placed in the middle of the game board. To this word, if possible, it is necessary to add the remaining letters so that at the intersection you get new words.</p>
                                <p>Then the next players must put their letters “across” or attach them to the words they have already made up.</p>
                                <p>Words are laid out either left to right or top to bottom.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav3">Vocabulary</h2>
                                <p>All words in the Cambridge English-Russian Dictionary (https://dictionary.cambridge.org/ru/ ), including the <a href="#" onclick="$('#abbr').css({ display: 'block' });return false;" style="cursor: pointer;" title="ad AGM AIDS ATM BA BBC BSc BSE CCTV CD CEO CFC Corp dab DIY DNA DVD EFL ELT er ESL FA FAQ FM GCSE GDP GMO GMT GNP GP GPS HIV HQ ICT IOU IPA IQ ISP it ITV IVF JP JPEG LAN LCD LPG MA MBA MEP MP MPV MRI MRSA Ms MSc MTV NATO OAP PC PDA PE pin POW PR pt QC ram RSI SARS SASE SATNAV SGML SIDS SMS SPF SUV TB TEFL TESOL TV UFO UK USA VAT VCR VDU VIP WC WMD www XML">most common abbreviations</a>, may be used.<span id="abbr" style="display:none;">ad AGM AIDS ATM BA BBC BSc BSE CCTV CD CEO CFC Corp dab DIY DNA DVD EFL ELT er ESL FA FAQ FM GCSE GDP GMO GMT GNP GP GPS HIV HQ ICT IOU IPA IQ ISP it ITV IVF JP JPEG LAN LCD LPG MA MBA MEP MP MPV MRI MRSA Ms MSc MTV NATO OAP PC PDA PE pin POW PR pt QC ram RSI SARS SASE SATNAV SGML SIDS SMS SPF SUV TB TEFL TESOL TV UFO UK USA VAT VCR VDU VIP WC WMD www XML</span></p>
                                <p>Only proper nouns in the singular (or in the plural if the word has no singular form) may be used.</p>
                                <p>To see what words players have made in previous turns, as well as their meaning and “cost”, click on the button <img src="/img/otjat/log2.svg" height="64"/></p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav4">Game play</h2>
                                <p>At the beginning of the game, each person is given 7 chips with printed letters on them. Several words can be placed in one turn. Each new word must be adjacent (have a common letter or letters) to the previously laid out words. Words are read only horizontally from left to right and vertically from top to bottom.</p>
                                <p>The first lined&nbsp;word&nbsp;must go through the center cell.</p>
                                <p>
                                You can send your combination by pressing the button <br /><img src="/img/otjat/otpravit2.svg" width="80%"/>
                                <br />
                                If it is not your move at the moment, the button will become inactive <br /><img src="/img/inactive/otpravit2.svg" width="80%"/>
                                <br />
                                If the SEND button starts blinking red - your turn time is running out. Hurry up and send your combination!
                                </p>
                                <p>If a player doesn't want to or can't lay out any words - he has the right to change any number of his letters, skipping a turn.
                                <br /><img src="/img/otjat/pomenyat2.svg" width="80%"/>
                                </p>
                                <p>Any sequence of letters horizontally and vertically must be a word. In other words, the game does not allow random letter combinations on the field that do not represent words that meet the above criteria.</p>
                                <p>After each turn you need to add new letters up to 7.</p>
                                <p>If a player has used all 7 letters during a turn, he is awarded an additional 15 score points.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav5">Distribution of chips and cost of letters</h2>
                                <table cellpadding="10" cellspacing="10">
                                    <tbody>
                                        <tr>
                                            <th>Letter</th>
                                            <th>Quantity</th>
                                            <th>Price (score points)</th>
                                        </tr>
                                        <tr>
                                            <td><strong>Wild (blank) card</strong></td>
                                            <td>10</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>A</td>
                                            <td>9</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>B</td>
                                            <td>2</td>
                                            <td>3 points</td>
                                        </tr>
                                        <tr>
                                            <td>C</td>
                                            <td>2</td>
                                            <td>3 points</td>
                                        </tr>
                                        <tr>
                                            <td>D</td>
                                            <td>4</td>
                                            <td>2 points</td>
                                        </tr>
                                        <tr>
                                            <td>E</td>
                                            <td>12</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>F</td>
                                            <td>2</td>
                                            <td>4 points</td>
                                        </tr>
                                        <tr>
                                            <td>G</td>
                                            <td>3</td>
                                            <td>2 points</td>
                                        </tr>
                                        <tr>
                                            <td>H</td>
                                            <td>2</td>
                                            <td>4 points</td>
                                        </tr>
                                        <tr>
                                            <td>I</td>
                                            <td>9</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>J</td>
                                            <td>1</td>
                                            <td>8 points</td>
                                        </tr>
                                        <tr>
                                            <td>K</td>
                                            <td>1</td>
                                            <td>5 points</td>
                                        </tr>
                                        <tr>
                                            <td>L</td>
                                            <td>4</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>M</td>
                                            <td>2</td>
                                            <td>3 points</td>
                                        </tr>
                                        <tr>
                                            <td>N</td>
                                            <td>6</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>O</td>
                                            <td>8</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>P</td>
                                            <td>2</td>
                                            <td>3 points</td>
                                        </tr>
                                        <tr>
                                            <td>Q</td>
                                            <td>1</td>
                                            <td>10 points</td>
                                        </tr>
                                        <tr>
                                            <td>R</td>
                                            <td>6</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>S</td>
                                            <td>4</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>T</td>
                                            <td>6</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>U</td>
                                            <td>4</td>
                                            <td>1 point</td>
                                        </tr>
                                        <tr>
                                            <td>V</td>
                                            <td>2</td>
                                            <td>4 points</td>
                                        </tr>
                                        <tr>
                                            <td>W</td>
                                            <td>2</td>
                                            <td>4 points</td>
                                        </tr>
                                        <tr>
                                            <td>X</td>
                                            <td>1</td>
                                            <td>8 points</td>
                                        </tr>
                                        <tr>
                                            <td>Y</td>
                                            <td>2</td>
                                            <td>4 points</td>
                                        </tr>
                                        <tr>
                                            <td>Z</td>
                                            <td>1</td>
                                            <td>10 points</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav6">Scoring and bonus points</h2>
                                <p>Each letter is assigned a number of score points from 1 to 10. Some cells on the board are painted in different colors. The number of points a player receives for a word is calculated as follows:</p>
                                <ul>
                                    <li>If cell under the letter is colorless, the number of score points written on the letter is added</li>
                                    <li>If cell is <span style="background-color:#A1ACCE;color:black;">light blue</span> - the number of score points of the <strong>letter</strong> is multiplied by <strong>2</strong></li>
                                    <li>If cell is <span style="background-color:#111143;color:white;">dark blue</span> - the number of score points of the <strong>letter</strong> is multiplied by <strong>3</strong></li>
                                    <li>If cell is <span style="background-color:#FF7B7B;color:black;">rose</span> - the number of score points of the whole <strong>word</strong> is multiplied by <strong>2</strong></li>
                                    <li>If cell is <span style="background-color:#E20000;color:white;">red</span> - the number of score points of the whole <strong>word</strong> is multiplied by <strong>3</strong></li>
                                </ul>
                                <p>If a word uses multipliers of both types, the doubling (tripling) of letter score points is counted before the doubling (tripling) of word score points.</p>
                            </div>
                            <div class="fon-right">
                                <h2 id="nav7">The Wild card</h2>
                                <p>Also, there are 10 wild cards in the set of chips. Such a chip can be used as any letter of the player's choice. For example, the player can put the word &laquo;P_ONE&raquo;, where the role of the letter &laquo;H&raquo; will be played by The Wild card.</p>
                                <p>As soon as the player places the Wild card on the field, the game will immediately offer to choose the letter to be replaced by it. When rearranging the Wild card the choice of letter will be offered again.</p>
                                <p>The Wild card gives as many score points as the letter whose role it plays.</p>
                                <h3>Reusing the Wild card</h3>
                                <p>If any player has a letter that is replaced by the Wild card on the playing board, he can replace that Wild card chip with his own letter and use the obtained Wild card to make a word, but only for the current turn. It is not possible to take Wild card from the field “to spare”.</p>
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
        T::EN_LANG => <<<EN
Elo rating
<br><br>
Elo rating system, Elo coefficient - a method of calculating the relative strength of players in games, 
in games involving two players (e.g., chess, checkers or shogi, go). 
<br>
This rating system was developed by Hungarian-born American physics professor Arpad Elo (Hungarian: Élő Árpád; 1903-1992)
<br><br>
The greater the difference in rating among the players, the fewer points to the rating the stronger player will get when winning.
<br> 
Conversely, a weaker player will get more points towards the rating if he defeats a stronger player.
<br><br>
Thus, it is more advantageous for a strong player to play with equals - if you win, you get more points, and if you lose, you don't lose very many points.
<br><br>
It is safe for a beginner to fight an experienced master.
<br>The loss of ranking if you lose will be small.
<br>But, in case of victory, the master will generously share the rank points 
<br><br>
When playing three and four player games, the new rating is calculated for each pair of players and the total number of rank points of its gain/loss is displayed.
EN
        ,
        T::RU_LANG => <<<RU
Рейтинг Эло
<br><br>
Система рейтингов Эло, коэффициент Эло — метод расчёта относительной силы игроков в играх, 
в которых участвуют двое игроков (например, шахматы, шашки или сёги, го). 
<br>
Эту систему рейтингов разработал американский профессор физики венгерского происхождения Арпад Эло (венг. Élő Árpád; 1903—1992)
<br><br>
Чем больше разница в рейтинге среди игроков, тем меньшее количество очков к рейтингу получит более сильный игрок при выигрыше.
<br> 
И наоборот, слабый игрок получит большее количество очков к рейтингу в случае победы над более сильным
<br><br>
Таким образом, сильному игроку выгоднее играть с равными - при выигрыше получаешь больше очков, а при проигрыше теряешь не очень много
<br><br>
Новичку безопасно сразиться с опытным мастером.
<br>Потеря рейтинга в случае проигрыша будет небольшой.
<br>Зато, в случае победы, мастер щедро поделится баллами 
<br><br>
При игре втроем и вчетвером новый рейтинг рассчитывается для каждой пары игроков и выводится итоговое количество очков его прибавки / потери. 
RU
        ,
    ];
    const REWARDS = [
        T::EN_LANG => <<<EN
Players are rewarded for certain achievements (records).
<br><br>
The player's awards are reflected in the “STATS” section in the following nominations: gold/silver/bronze/stone.
<br><br>
When receiving a reward card, the player receives a bonus of SUDOKU coins {{sudoku_icon}}<br> 
Coins can be used in a special game mode “ON Coins”, you can replenish your in-game wallet, 
as well as withdraw coins from the game - read more in the “Coin Game mode” tab
<br><br>
<h2>List of achievements and their coin values</h2>

<table class="table table-dark table-transp">
<thead>
RANKING POSITION
</thead>
<tr>
<td>
Type
</td>
<td>
Name
</td>
<td>
Reward
</td>
<td>
Income<br> per hour
</td>
</tr>
<tr>
<td>
gold
</td>
<td>
TOP 1
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
silver
</td>
<td>
TOP 2
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
bronze
</td>
<td>
TOP 3
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
stone
</td>
<td>
Best 10
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
GAME POINTS
</thead>
<tr>
<td>
Type
</td>
<td>
Name
</td>
<td>
Reward
</td>
<td>
Income<br> per hour
</td>
</tr>
<tr>
<td>
gold
</td>
<td>
RECORD OF THE YEAR
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
silver
</td>
<td>
RECORD OF THE MONTH
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
bronze
</td>
<td>
RECORD OF THE WEEK
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
stone
</td>
<td>
RECORD OF THE DAY
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
TURN POINTS
</thead>
<tr>
<td>
Type
</td>
<td>
Name
</td>
<td>
Reward
</td>
<td>
Income<br> per hour
</td>
</tr>
<tr>
<td>
gold
</td>
<td>
RECORD OF THE YEAR
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
silver
</td>
<td>
RECORD OF THE MONTH
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
bronze
</td>
<td>
RECORD OF THE WEEK
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
stone
</td>
<td>
RECORD OF THE DAY
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
WORD POINTS
</thead>
<tr>
<td>
Type
</td>
<td>
Name
</td>
<td>
Reward
</td>
<td>
Income<br> per hour
</td>
</tr>
<tr>
<td>
gold
</td>
<td>
RECORD OF THE YEAR
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
silver
</td>
<td>
RECORD OF THE MONTH
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
bronze
</td>
<td>
RECORD OF THE WEEK
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
stone
</td>
<td>
RECORD OF THE DAY
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
LONGEST WORD
</thead>
<tr>
<td>
Type
</td>
<td>
Name
</td>
<td>
Reward
</td>
<td>
Income<br> per hour
</td>
</tr>
<tr>
<td>
gold
</td>
<td>
RECORD OF THE YEAR
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
silver
</td>
<td>
RECORD OF THE MONTH
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
bronze
</td>
<td>
RECORD OF THE WEEK
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
stone
</td>
<td>
RECORD OF THE DAY
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
GAMES PLAYED
</thead>
<tr>
<td>
Type
</td>
<td>
Name
</td>
<td>
Reward
</td>
<td>
Income<br> per hour
</td>
</tr>
<tr>
<td>
gold
</td>
<td>
RECORD OF THE YEAR
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
silver
</td>
<td>
RECORD OF THE MONTH
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
bronze
</td>
<td>
RECORD OF THE WEEK
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
stone
</td>
<td>
RECORD OF THE DAY
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
INVITED FRIENDS (REFERRALS)
</thead>
<tr>
<td>
Type
</td>
<td>
Name
</td>
<td>
Reward
</td>
<td>
Income<br> per hour
</td>
</tr>
<tr>
<td>
gold
</td>
<td>
RECORD OF THE YEAR
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
silver
</td>
<td>
RECORD OF THE MONTH
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
bronze
</td>
<td>
RECORD OF THE WEEK
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
stone
</td>
<td>
RECORD OF THE DAY
</td>
<td>
{{sudoku_icon}} {{stone_reward}}
</td>
<td>
{{sudoku_icon}} {{stone_income}}
</td>
</tr>
</table>

As long as one player's record has not been broken by another player, the reward card is reflected for that player in the “ACTIVE AWARDS” tab of the “STATS” section.<br><br>
Each “ACTIVE Reward” every hour generates additional
“profit” in coins.<br><br>
If a record has been broken by another player, the award card of the
the previous owner of the record is moved to the “PAST AWARDS” tab and stops bringing passive income.
Rewards” tab and ceases to bring passive income.<br><br>
The total number of coins received (one-time bonuses and
additional profit) can be viewed in the “PROFILE” section in the “Wallet” tab in the “SUDOKU balance” and “Bonuses accrued” fields, respectively.
tab “Wallet” in the field “SUDOKU balance” and “Bonuses accrued” respectively.<br><br>
When exceeding the own record for the achievements “PLAYED PARTIES” and “APPLIED FRIENDS
games” and ‘invited friends’ achievements, the player is not given a new reward card or coins again.
reward card and coins are not awarded again. The record value itself
(number of games / number of friends) is updated on the reward card.<br><br>
For example, if a player has previously earned the achievement - “GAMES PLAYED”
(gold) for 10,000 games, then when the number of games of this player is changed to 10,001 another award card will not be given to the record holder.
10,001, no more reward cards will be issued to the record holder.<br>
EN,
        T::RU_LANG => <<<RU
За определенные достижения (рекорды) игроки получают награды.
<br><br>
Награды игрока отражаются в разделе "СТАТИСТИКА" в следующих
номинациях: золото/серебро/бронза/камень.
<br><br>
При полученнии карточки-награды игроку также начисляется бонус монетами SUDOKU {{sudoku_icon}}<br> 
Использовать монеты можно в режиме игры "НА МОНЕТЫ", можно пополнять внутриигровой кошелек - подробнее читайте во вкладке "ИГРА НА МОНЕТЫ"
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
"АКТИВНАЯ НАГРАДА" каждый час генерирует дополнительную
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
        T::EN_LANG => <<<EN
Coin <strong>SUDOKU</strong> {{sudoku_icon}} is an in-game currency for a network of games - <strong>Erudite, Scrabble, Sudoku</strong> (coming soon)<br><br>
One account for all games, one currency, one wallet<br><br>
In the crypto world, the coin is also called SUDOKU. Soon it will be possible to withdraw any number of SUDOKU coins from your in-game wallet to an external wallet in the TON (Telegram) network
<br><br>
In the meantime, we try to win as many coins as possible in the game by selecting the “Coins” mode<br><br>

This mode also takes into account and accrues player rankings.<br>
However, coins won by the results of the game are now credited to your wallet (or deducted if you lose)
<br><br>
Depending on the current balance of coins in your wallet, you are offered to play for 1, 5, 10, etc. coins - choose the desired amount from the list
<br><br>
After pressing the “Start” button, the search for an opponent who is also ready to bet the specified amount will begin
<br><br> 
For example, you have specified your bet size as 5 coins, and among those starting a new game there are only those willing to bet 1 coin.
<br>
Then the bet for both you and such a player will be 1 coin - the lesser of both options.
<br><br>
In case there is someone willing to fight for 10 coins, your bet - 5 will be selected and the game will start with a bank of 10 coins - 5+5
<br><br>
In a two-person game, the winner gets the entire pot - his bet and his opponent's bet
<br><br>
In a three-way game, the winner takes his bet and the bet of the last player (the player with the fewest points). 
The middle player (the runner-up) gets his bet back, keeping his coins
<br><br>
In a four-player game, the pot is split between players in 1st and 4th place (the first player takes both bets), 
and players in 2nd and 3rd places (the second takes both bets).
<br><br>
Thus, playing three and four becomes less risky in terms of saving coins
<br><br>
If all losing players have the same number of points, then the winning player takes all bets
<br><br>
In a foursome game, if the 2nd and 3rd players score an equal number of points, they get their bet back, keeping their bets
<br><br>
New Rank in all cases is calculated as usual - see the “Ranking” tab
<br><br>
<h2>How you can replenish your wallet</h2>
<ol>
<li>
Every new player receives welcome {{stone_reward}} SUDOKU coins to his balance and can immediately get involved in the race for big wins
</li>
<li>
You will receive {{stone_reward}} coins for each friend who comes to the game using your referral link. 
Also, by setting a record (for the day, week, month, year) on the number of invitees, you will be rewarded. To invite a user, you need to log in to the game via Telegram.
</li>
<li>
Coins are awarded for achievements in the game (points per game, points per move, points per word, number of games, number of invitees, place in the ranking from #1 to #10)
</li>
<li>
For every 100 games, {{stone_reward}} of SUDOKU coins are awarded
</li>
<li>
Buy coins for rubles by transfer
</li>
<li>Buy Coins for Cryptocurrency (coming soon)
</li>
</ol>

<br>
The number of coins awarded for each achievement may change over time, either up or down. The actual reward is reflected in the achievement card.
<br><br>
<h2>What you can do with the coins you win</h2>
<ol>
<li>
Play our games, increasing the stakes, adding excitement and interest to your favorite pastime
</li>
<li>
Sell coins for rubles or for cryptocurrency (soon) and get your reward in real money terms
</li>
<li>
Make a gift to another player by sending the latter any number of coins from your wallet (coming soon)
</li>     
</ol>
<br>
You can find out the details of your wallet balance in the “Wallet” tab of the “PROFILE” menu.
<br><br>
<strong>Bonuses accrued</strong> - the result of passive earnings accrued every hour depending on the player's achievements (menu “STATS”, section “Awards”).
<br>Bonuses can be transferred to the balance by pressing the “REMOVE” button (soon)
<br><br>
<strong>SUDOKU balance</strong> - current balance of coins without bonuses. Coins are deducted / credited from it according to the results of the game
<br><br>
Achievement cards akin to medals are a marker of your success. 
<br>They include the name of the achievement, the period (year, day, week, month), the number of points (rating, word length, number of friends), and the number of coins 
<br><br>
Passive coin earning stops when your record is broken by another player
EN
        ,
        T::RU_LANG => <<<RU
Монета <strong>SUDOKU</strong> {{sudoku_icon}} - это внутриигровая валюта для сети игр - <strong>Эрудит, Scrabble, Sudoku</strong> (скоро)<br><br>
Один аккаунт на все игры, одна валюта, один кошелек<br><br>
В крипто-мире монета также называется SUDOKU. Скоро станет возможным вывести любое количество монет SUDOKU со своего игрового кошелька на внешний кошелек сети TON (Телеграм)
<br><br>
А пока мы стараемся выиграть как можно больше монет в игре, выбирая режим "На монеты"<br><br>

Данный режим также учитывает и начисляет рейтинг игроков.<br>
Однако, теперь на ваш кошелек начисляются выигранные по результатам партии монеты (или списываются в случае проигрыша)
<br><br>
В зависимости от текущего баланса монет в кошельке, вам предлагается сыграть на 1, 5, 10 и т.д. монет - выберите желаемое количество из списка
<br><br>
После нажатия кнопки "Начать" начнется поиск соперника, также готового поставить указанную сумму
<br><br> 
Например, вы указали размер своей ставки 5 монет, а среди начинающих новую игру есть только желающие поставить 1 монету.
<br>
Тогда ставка и для вас и для такого игрока составит 1 монету - меньшее из обоих вариантов.
<br><br>
В случае, если есть желающий сразиться на 10 монет, будет выбрана ваша ставка - 5, и начнется игра с банком в 10 монет - 5+5
<br><br>
При игре на двоих выигравший получает весь банк - свою ставку и ставку противника
<br><br>
При игре втроем, выигравший забирает свою ставку и ставку последнего игрока (игрока с наименьшим количеством очков). 
Средний игрок (занявший второе место) получает обратно свою ставку, оставшись при своих монетах
<br><br>
При игре вчетвером, банк делят игроки на 1-м и 4-м местах (первый забирает обе ставки), 
и игроки на 2-м и 3-м местах (второй забирает обе ставки)
<br><br>
Таким образом, игра на троих и на четверых становится менее рискованной с точки зрения сохранения монет
<br><br>
Если все проигравшие игроки набрали одинаковое количество очков, тогда выигравший забирает все ставки
<br><br>
При игре вчетвером, если 2-й и 3-й игроки набирают равное количество очков, они получают обратно свою ставку, оставаясь при своих
<br><br>
Рейтинг во всех случаях рассчитывается как обычно - смотри вкладку "Рейтинг"
<br><br>
<h2>Каким образом можно пополнить кошелек</h2>
<ol>
<li>
Каждый новый игрок получает приветственные {{stone_reward}} монет SUDOKU на свой баланс и сразу может включиться в гонку за большими выигрышами
</li>
<li>
За каждого друга, пришедшего в игру по вашей реферальной ссылке, вы получите {{stone_reward}} монет. 
Также, установив рекорд (за день, неделю, месяц, год) по числу приглашенных, вам будет начисляться награда. Чтобы пригласить пользователя, вам нужно зайти в игру через Телеграм.
</li>
<li>
За достижения в игре (очки за игру, очки за ход, очки за слово, количество партий, количество приглашенных, место в рейтинге от №1 до №10) начисляются монеты
</li>
<li>
За каждые 100 игр начисляется {{stone_reward}} монет SUDOKU
</li>
<li>
Купить монеты за рубли переводом
</li>
<li>Купить монеты за криптовалюту (скоро)
</li>
</ol>

<br>
Количество монет, начисляемых за каждое достижение может меняться со временем как в большую, так и в меньшую сторону. Фактическая награда отражается в карточке достижения.
<br><br>
<h2>Что можно делать с выигранными монетами</h2>
<ol>
<li>
Играть в наши игры, увеличивая ставки, добавляя азарт и интерес в любимое времяпрепровождение
</li>
<li>
Продать монеты за рубли или за криптовалюту (скоро) и получить свою награду в реальном денежном выражении
</li>
<li>
Сделать подарок другому игроку, отправив последнему любое количество монет со своего кошелька (скоро)
</li>     
</ol>
<br>
Узнать детали баланса своего кошелька вы можете во вкладке "КОШЕЛЕК" меню "ПРОФИЛЬ".
<br><br>
<strong>Начислено бонусов</strong> - результат пассивного заработка, начисляемого каждый час в зависимости от достижений игрока (меню "СТАТИСТИКА", раздел "ДОСТИЖЕНИЯ").
<br>Бонусы можно перенести в баланс по кнопке "ЗАБРАТЬ" (скоро)
<br><br>
<strong>Баланс SUDOKU</strong> - текущий баланс монет без учета бонусов. Из него списываются / начисляются монеты по результатам игры
<br><br>
Карточки достижений сродни медалям являются отметкой о ваших успехах. 
<br>В них указывается название достижения, период (год, день, неделя, месяц), количество очков (рейтинг, длина слова, число друзей), количество монет 
<br><br>
Пассивный заработок монет прекращается, когда ваш рекорд перебит другим игроком
RU
        ,
    ];
}