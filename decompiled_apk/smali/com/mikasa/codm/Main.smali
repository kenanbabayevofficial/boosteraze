.class public Lcom/mikasa/codm/Main;
.super Ljava/lang/Object;


# static fields
.field private static a:Ljava/lang/String;

.field private static final short:[S


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0x6a

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/Main;->short:[S

    return-void

    :array_0
    .array-data 2
        0x261s
        0x262s
        0x263s
        0x264s
        0x265s
        0x266s
        0x267s
        0x268s
        0x269s
        0x26as
        0x26bs
        0x26cs
        0x26ds
        0x26es
        0x26fs
        0x270s
        0x271s
        0x272s
        0x273s
        0x274s
        0x275s
        0x276s
        0x277s
        0x278s
        0x279s
        0x27as
        0x241s
        0x242s
        0x243s
        0x244s
        0x245s
        0x246s
        0x247s
        0x248s
        0x249s
        0x24as
        0x24bs
        0x24cs
        0x24ds
        0x24es
        0x24fs
        0x250s
        0x251s
        0x252s
        0x253s
        0x254s
        0x255s
        0x256s
        0x257s
        0x258s
        0x259s
        0x25as
        0x230s
        0x20cs
        0x205s
        0x201s
        0x213s
        0x205s
        0x240s
        0x217s
        0x201s
        0x209s
        0x214s
        0x24es
        0x24es
        0x24es
        0x366s
        0x351s
        0x351s
        0x34cs
        0x351s
        0x319s
        0x303s
        0x365s
        0x34as
        0x34fs
        0x346s
        0x303s
        0x36ds
        0x34cs
        0x357s
        0x303s
        0x365s
        0x34cs
        0x356s
        0x34ds
        0x347s
        0x303s
        0x34cs
        0x351s
        0x303s
        0x36ds
        0x34cs
        0x303s
        0x360s
        0x34cs
        0x34ds
        0x34ds
        0x346s
        0x340s
        0x357s
        0x34as
        0x34cs
        0x34ds
        0x303s
        0x302s
    .end array-data
.end method

.method private static native CheckOverlayPermission(Landroid/content/Context;)V
.end method

.method static synthetic a()Ljava/lang/String;
    .locals 1

    invoke-static {}, Lcom/mikasa/codm/Main;->۟۟ۦۣۧ()Ljava/lang/String;

    move-result-object v0

    return-object v0
.end method

.method public static native a(I)Ljava/lang/String;
.end method

.method public static native a(Landroid/content/Context;)V
.end method

.method public static native a(Landroid/content/Context;ZZZLjava/lang/String;Ljava/lang/String;)V
.end method

.method public static native a(Ljava/io/File;)V
.end method

.method static synthetic a(Ljava/lang/String;)V
    .locals 2

    invoke-static {p0}, Lcom/mikasa/codm/Main;->۟ۢۧۡۥ(Ljava/lang/Object;)V

    invoke-static {}, Lcom/mikasa/codm/ۧۡ۟ۦ;->ۧ۟۠ۡ()I

    move-result v1

    const/16 v0, 0x650

    :goto_0
    xor-int/lit16 v0, v0, 0x661

    sparse-switch v0, :sswitch_data_0

    goto :goto_0

    :cond_0
    :sswitch_0
    const/16 v0, 0x68e

    goto :goto_0

    :sswitch_1
    if-gtz v1, :cond_0

    const/16 v0, 0x6ad

    goto :goto_0

    :sswitch_2
    const-string v0, "S1p"

    invoke-static {v0}, Lcom/mikasa/codm/ۧۡ۟ۦ;->۠ۢۤۥ(Ljava/lang/String;)Ljava/lang/String;

    move-result-object v0

    invoke-static {v0}, Ljava/lang/Integer;->decode(Ljava/lang/String;)Ljava/lang/Integer;

    move-result-object v0

    sget-object v1, Ljava/lang/System;->out:Ljava/io/PrintStream;

    invoke-virtual {v1, v0}, Ljava/io/PrintStream;->println(Ljava/lang/Object;)V

    :sswitch_3
    return-void

    nop

    :sswitch_data_0
    .sparse-switch
        0xe -> :sswitch_0
        0x31 -> :sswitch_1
        0xcc -> :sswitch_2
        0xef -> :sswitch_3
    .end sparse-switch
.end method

.method public static native a(Ljava/lang/String;Ljava/lang/String;)Z
.end method

.method public static native b(Landroid/content/Context;)V
.end method

.method private static native b(Ljava/lang/String;)V
.end method

.method public static native ۟۟ۦۣۧ()Ljava/lang/String;
.end method

.method public static native ۟ۢۢۧۧ(Ljava/lang/Object;)V
.end method

.method public static native ۟ۢۧۡۥ(Ljava/lang/Object;)V
.end method

.method public static native ۥۥۢۥ()[S
.end method
