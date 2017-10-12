<?php

namespace Vifeed\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FaqController extends Controller
{
    public function indexAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(60 * 60 * 10);

        $faq = [
          'advertiser' => [
              [
                'q' => 'Что из себя представляет Zombakka?',
                'a' => 'Zombakka представляет из себя очень надежную современную систему по продвижению различных видео роликов. С помощью этого сервиса люди размещают свой видео контент на многих популярных сайтах, а также в социальных сетях. Можно размещаться и в Одноклассниках, и в Вконтакте, и в Фейсбуке, а также во многих других местах. Оплачиваются только лишь уникальные просмотры.'
              ],
              [
                'q' => 'Каким образом продвигается видео, как обеспечиваются просмотры?',
                'a' => 'Сервис работает только с самыми лучшими сайтами, а также с сообществами в разных социальных сетях. Веб-мастера размещают видео контент с сервиса на своих площадках, получая оплату за каждый индивидуальный, уникальный просмотр их посетителей размещенного ролика. И если раньше нужно было отдельно договариваться с каждой площадкой о чем-то конкретно, то сейчас можно легко разместить свой ролик через эту удобную систему и оставить все заботы по его продвижению нашей компании.'
              ],
              [
                'q' => 'В каком формате размещены все ролики?',
                'a' => 'Они размещаются с помощью фирменного плеера YouTube. Кроме того, скоро будет реализовано размещение с помощью плееров Coub и Vimeo.'
              ],
              [
                'q' => 'Как именно размещаются плееры с этими рекламными роликами?',
                'a' => 'Их размещают в качестве отдельных постов. Бывают исключения — можно размещать прямо внутри постов или под самым основным контентом портала, а также в виде записей или постов на стене (в социальных сетях).'
              ],
              [
                'q' => 'Опишите процесс создания и самого старта рекламной компании в этом сервисе.',
                'a' => '1) Рекламодатель регистрируется в системе, указывая ссылку на то видео, которое нужно продвигать. Он же выбирает целевую аудиторию.
2) Веб-мастера получают о новом видео уведомление по SMS или на почту. Если их все устраивает, они размещают у себя видео.
3) Рекламодатель получает возможность следить за статистикой компании в реальном времени.'
              ],
              [
                'q' => 'Скажите, а учитываются только реальные просмотры?',
                'a' => 'Конечно, учитывается лишь один просмотр с одного уникального посетителя. Оплата идет за те ролики, просмотр которых занял более пяти секунд.'
              ],
              [
                'q' => 'Какие способы оплаты существуют в сервисе?',
                'a' => 'Сервис работает по безналичному расчету, принимаются и электронные деньги веб-мани, яндекс, киви и другие.'
              ],
              [
                'q' => 'Возможно ли сделать кампанию в другую страну?',
                'a' => 'Да, это возможно и обсуждается, рекламные кампании в другие страны вполне возможны.'
              ],
              [
                'q' => 'Моего вопроса нет в этом FAQ, где я могу получить оперативную помощь?',
                'a' => 'Вы можете написать нам на почту. Мы быстро ответим на все ваши вопросы.'
              ]
            ],
            'publisher' => [
              [
                'q' => 'Что такое Zombakka?',
                'a' => 'Zombakka является современной и весьма надежной системой по продвижению видео контента. С помощью нее можно распространять ролики на множестве популярных сайтов, а также в сообществах социальных сетей, таких, например, как Facebook, Одноклассники или Вконтакте. Система построена таким образом, что вы оплачиваете только уникальные просмотры.'
              ],
              [
                'q' => 'Как именно можно зарабатывать в этой системе?',
                'a' => 'Заработок идет за счет размещения видео контента у Вас на ресурсе, в посещаемых группах, пабликах, сайтах, блогах и т.д. Оплата идет за каждый уникальный просмотр видео в течении всего периода рекламной кампании.'
              ],
              [
                'q' => 'Какой тематики видео контент на сервисе?',
                'a' => 'Тематика видео контента весьма разнообразная, но больше всего представлено вирусных роликов и роликов с социальной рекламой.'
              ],
              [
                'q' => 'Как часто появляются новые рекламные кампании?',
                'a' => 'Это зависит от активности рекламодателей. В определенные периоды доступно большое количество роликов одновременно, а в иные времена их достаточно мало — рекламодатели руководствуются своими принципами работы и скидывают контент неравномерно.
У нас есть целый ряд специально разработанных инструментов, которые позволяют всем партнерам компании оперативно включаться в рекламную активность. Например, рассылаются уведомления по SMS или на E-mail.'
              ],
              [
                'q' => 'Нужно ли просматривать ролики до конца для зачета просмотра?',
                'a' => 'Нет, просмотр засчитывается через пять секунд после удержания ролика.'
              ],
              [
                'q' => 'Как часто система производит выплаты и как именно?',
                'a' => 'Выплаты производятся по запросу, прямо на электронные кошельки веб-мани или Яндекс.Деньги, Киви или другие системы электронных платежей. Минимальная сумма к выплате - от тысячи рублей.'
              ],
              [
                'q' => 'Есть ли определенные требования к площадкам-участникам?',
                'a' => 'Система принимает к участию все сайты и порталы, а также публичные страницы и группы в социальных сетях. Обязательное требование только одно — наличие на странице счетчика Google Analytics, либо Яндекс.Метрика. Хозяин сайта должен предоставить доступ ко всей детальной статистике.'
              ],
              [
                'q' => 'Можно ли удалить свое видео с площадки тогда, когда захочется?',
                'a' => 'Да, можно без проблем удалить свое видео в любое время.'
              ],
              [
                'q' => 'Сколько роликов можно одновременно разместить в системе?',
                'a' => 'Сколько угодно, так как количество размещенных роликов неограниченно. Вы можете легко поступать так, как вам это будет удобно.'
              ],
              [
                'q' => 'Можно ли добавить видео для одной площадки, а в других пабликах сделать репост?',
                'a' => 'Нет, видео должно быть только для той площадки, для которой оно и предназначено. Возможности сделать репост нет.'
              ],
              [
                'q' => 'Сколько всего площадок можно повесить на один аккаунт?',
                'a' => 'На один аккаунт вы можете повесить сколько угодно площадок. Главное условие для работы — все площадки должны соответствовать правилам сервиса.'
              ],
              [
                'q' => 'Можно ли получить бан за нарушение правил сервиса?',
                'a' => 'Да, за грубые нарушения можно получить бан, а за терпимые — предупреждение.'
              ],
              [
                'q' => 'Как узнать, что в сервисе появилось новое видео для размещения?',
                'a' => 'Для этого нужно подписаться на рассылку с уведомлениями — это SMS и E-mail рассылка.'
              ],
              [
                'q' => 'Моего вопроса нет в этом FAQ, куда мне нужно обратиться за помощью?',
                'a' => 'Для этого вы должны написать нам на почту — на все вопросы будет дан оперативный ответ.'
              ]
            ]
        ];

        return $this->render('VifeedFrontendBundle:Public:faq.html.twig', [
          'faq' => $faq
        ], $response);
    }
}