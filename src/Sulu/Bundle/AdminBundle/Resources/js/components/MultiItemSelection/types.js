// @flow
export type Button = {|
    disabled?: boolean,
    icon?: string,
    label?: string,
    onClick: (value: ?string) => void,
    options?: Array<ButtonOption>,
|};

type ButtonOption = {
    label: string,
    value: string,
};