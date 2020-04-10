# Import fields

## Normal field clone

- Điền số lần lặp của clone vào dòng trên
- Dòng dưới đền giá trị
- Thay đổi giá trị [1] => [_i_]
- VD: https://imgur.com/w1F6bwM

```
4
{tickets[1]/ticket[_i_]/@title}
```

## Group clone

- Điền số lần lặp vào dòng nhập giá trị của group
- Điền các giá trị của các field con
- Thay đổi giá trị [1] => [_i_]
- VD: https://imgur.com/Tjep3Vm

```
group: 4
field_1: {tickets[1]/ticket[_i_]/@title}
field_2: {tickets[1]/ticket[_i_]/@price}
```