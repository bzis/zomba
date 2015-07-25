// describe('platform list', function () {
//   // beforeEach(function () {
//   //   browser.get('/');
//   //   element(by.id('login-link')).click();

//   //   element(by.name('email')).sendKeys('bangodrop2@gmail.com');
//   //   element(by.name('password')).sendKeys('test321');
//   //   element(by.id('signin-button')).click();

//   //   // element(by.tagName('h1')).getText().then(function (text) {
//   //   //   return text === 'Новая рекламная площадка';
//   //   // });
//   // });
//   it('should be shown if a user is signed in', function () {
//     browser.get('/');
//     element(by.id('signin-link')).click();
//     element(by.name('email')).sendKeys('test@mail.com');
//     element(by.name('password')).sendKeys('test321');
//     element(by.id('signin-button')).click();

//     browser.sleep(1000);

//     element(by.tagName('h1')).getText().then(function (text) {
//       return text === 'Мои финансы';
//     });
//   });

//   it('should have header on the page', function () {
//     // browser.sleep(500);
//     browser.get('/#/platform/list');
//     // expect(browser.getTitle()).toBe('Starter');
//     // element(by.id('login-link')).click();
//     expect(element(by.tagName('h1')).getText()).toMatch('Мои площадки');

//     // expect(element(by.name('form')).isPresent()).toBe(true);

//     //   var password = element( by.id( "password" ) );
//     // login.sendKeys(process.env.USERNAME );
//     // password.sendKeys(process.env.PASSWORD)
//   });

//   it('should have 2 or more elements', function () {
//     browser.get('/#/platform/list');
//     var list = element.all(by.css('tbody tr'));
//     expect(list.count()).toBe(2);
//   });
// });
